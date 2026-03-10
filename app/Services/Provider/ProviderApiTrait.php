<?php

namespace App\Services\Provider;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProviderException;

trait ProviderApiTrait
{
    protected function providerRequest(string $method, string $param, array $body = []): array
    {
        $response = Http::timeout(10)
            ->retry(3, 500)
            ->withBasicAuth(
                config('provider.username'),
                config('provider.password')
            )
            ->withHeaders([
                'mtd' => strtoupper($method),
                'param' => $param,
                'Content-Type' => 'application/json'
            ])
            ->send($method, config('provider.base_url'), [
                'json' => $body
            ]);

        if (!$response->successful()) {
            Log::error('Provider HTTP Error', [
                'param' => $param,
                'body' => $body,
                'response' => $response->body()
            ]);

            throw new ProviderException('Provider service unavailable.');
        }

        $data = $response->json();

        if (isset($data['error']) && $data['error'] != 0) {
            Log::warning('Provider Business Error', [
                'param' => $param,
                'body' => $body,
                'response' => $data
            ]);

            throw new ProviderException(
                $data['error_message'] ?? 'Provider business error.'
            );
        }

        return $data['result'] ?? $data;
    }

    // =========================
    // ORGANIZATION BY INN
    // =========================
    public function findOrganizationByInn(string $inn): array
    {
        return $this->providerRequest(
            'POST',
            '/api/provider/inn',
            ['inn' => $inn]
        );
    }

    // =========================
    // PERSON BY PASSPORT + BIRTHDATE
    // =========================
    public function findPersonByPassport(string $document, string $birthDate): array
    {
        return $this->providerRequest(
            'POST',
            '/api/provider/passport-birth-date-v2',
            [
                'transactionId' => now()->timestamp,
                'isConsent' => 'Y',
                'senderPinfl' => config('provider.sender_pinfl'),
                'document' => $document,
                'birthDate' => $birthDate
            ]
        );
    }

    // =========================
    // PERSON BY PINFL
    // =========================
    public function findPersonByPinfl(string $pinfl, string $document): array
    {
        return $this->providerRequest(
            'POST',
            '/api/provider/pinfl-v2',
            [
                'transactionId' => now()->timestamp,
                'isConsent' => 'Y',
                'senderPinfl' => config('provider.sender_pinfl'),
                'document' => $document,
                'pinfl' => $pinfl
            ]
        );
    }

    // =========================
    // VEHICLE
    // =========================
    public function findVehicle(string $seria, string $number, string $govNumber): array
    {
        return $this->providerRequest(
            'POST',
            '/api/provider/osago/vehicle',
            [
                'techPassportSeria' => $seria,
                'techPassportNumber' => $number,
                'govNumber' => $govNumber
            ]
        );
    }

    // =========================
    // CALCULATE (direct URL, extensible per product)
    // =========================
    protected function calcRequest(string $url, array $body): array
    {
        $response = Http::timeout(15)
            ->retry(3, 500)
            ->withBasicAuth(
                config('provider.username'),
                config('provider.password')
            )
            ->post($url, $body);

        if (!$response->successful()) {
            Log::error('Calc HTTP Error', [
                'url'      => $url,
                'body'     => $body,
                'response' => $response->body(),
            ]);

            throw new ProviderException('Calculation service unavailable.');
        }

        $data = $response->json();

        // result: 0 = success, non-zero = business error
        if (($data['result'] ?? -1) !== 0) {
            Log::warning('Calc Business Error', [
                'url'      => $url,
                'body'     => $body,
                'response' => $data,
            ]);

            throw new ProviderException($data['result_message'] ?? 'Calculation error.');
        }

        // Return first policy result
        return $data['policies'][0] ?? [];
    }

    // =========================
    // OSGOP CALCULATE
    // =========================
    public function calculateOsgop(int $insuranceTermId, int $vehicleTypeId, int $numberOfSeats): array
    {
        return $this->calcRequest(
            config('provider.calc.osgop'),
            [
                'policies' => [
                    [
                        'insuranceTermId' => $insuranceTermId,
                        'objects' => [
                            [
                                'vehicle' => [
                                    'vehicleTypeId' => $vehicleTypeId,
                                    'numberOfSeats' => $numberOfSeats,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    // =========================
    // OSGOP SUBMIT
    // =========================
    public function submitOsgop(array $applicant, array $vehicle, array $calculation): array
    {
        $raw        = $calculation['raw'] ?? [];
        $regionId   = $this->osgopApplicantRegionId($applicant);
        // Vehicle regionId: fallback to applicant's region if API returned 0
        $vehicleRegionId = (string) (($vehicle['region_id'] ?? 0) ?: $regionId);

        $body = [
            'number'            => date('dmy') . '-' . now()->timestamp,
            'sum'               => (string) ($calculation['insurance_sum'] ?? 0),
            'contractStartDate' => $calculation['start_date'],
            'contractEndDate'   => $calculation['end_date'],
            'regionId'          => (string) $regionId,
            'areaTypeId'        => '1',
            'agencyId'          => config('provider.agency_id'),
            'comission'         => '0',
            'insurant'          => $this->buildOsgopInsurant($applicant),
            'policies'          => [
                [
                    'startDate'          => $calculation['start_date'],
                    'endDate'            => $calculation['end_date'],
                    'insuranceSum'       => (string) ($calculation['insurance_sum'] ?? 0),
                    'insuranceRate'      => (string) ($raw['insuranceRate'] ?? $raw['rate'] ?? '0'),
                    'insurancePremium'   => (string) ($calculation['insurance_premium'] ?? 0),
                    'insuranceTermId'    => (int) $calculation['insurance_term_id'],
                    'healthLifeDamageSum' => (int) config('provider.osgop.health_life_damage_sum'),
                    'propertyDamageSum'  => (int) config('provider.osgop.property_damage_sum'),
                    'objects'            => [
                        [
                            'vehicle' => [
                                'isForeign'       => (bool) ($vehicle['is_foreign'] ?? false),
                                'techPassport'    => [
                                    'seria'  => $vehicle['tech_passport_seria']  ?? null,
                                    'number' => $vehicle['tech_passport_number'] ?? null,
                                ],
                                'govNumber'       => $vehicle['gov_number']        ?? null,
                                'regionId'        => $vehicleRegionId,
                                'modelCustomName' => $vehicle['model_custom_name'] ?? null,
                                'vehicleTypeId'   => (string) ($vehicle['vehicle_type_id'] ?? ''),
                                'issueYear'       => (string) ($vehicle['issue_year'] ?? ''),
                                'bodyNumber'      => $vehicle['body_number']       ?? null,
                                'numberOfSeats'   => (string) ($vehicle['number_of_seats'] ?? ''),
                                'engineNumber'    => $vehicle['engine_number']     ?? null,
                                'license'         => [
                                    'seria'     => $vehicle['license']['seria']     ?? null,
                                    'number'    => $vehicle['license']['number']    ?? null,
                                    'beginDate' => $vehicle['license']['beginDate'] ?? null,
                                    'endDate'   => $vehicle['license']['endDate']   ?? null,
                                    'typeCode'  => $vehicle['license']['typeCode']  ?? null,
                                ],
                                'ownerOrganization'  => $applicant['type'] === 'organization'
                                    ? [
                                        'inn'                => $applicant['organization']['inn']                ?? null,
                                        'name'               => $applicant['organization']['name']               ?? null,
                                        'representativeName' => $applicant['organization']['representativeName'] ?? null,
                                        'address'            => $applicant['organization']['address']            ?? null,
                                        'oked'               => $applicant['organization']['oked']               ?? null,
                                        'position'           => $applicant['organization']['position']           ?? null,
                                        'phone'              => $applicant['organization']['phone']              ?? null,
                                        'regionId'           => (string) ($applicant['organization']['regionId'] ?? '10'),
                                        'ownershipFormId'    => (string) ($applicant['organization']['ownershipFormId'] ?? '130'),
                                    ]
                                    : null,
                                'ownerPerson'        => $applicant['type'] === 'person'
                                    ? [
                                        'passportData' => [
                                            'pinfl'  => $applicant['person']['pinfl']           ?? null,
                                            'seria'  => $applicant['person']['passport_seria']  ?? null,
                                            'number' => $applicant['person']['passport_number'] ?? null,
                                        ],
                                        'fullName' => [
                                            'firstname'  => $applicant['person']['firstname']  ?? null,
                                            'lastname'   => $applicant['person']['lastname']   ?? null,
                                            'middlename' => $applicant['person']['middlename'] ?? null,
                                        ],
                                        'regionId'            => (string) ($applicant['person']['region_id'] ?? '10'),
                                        'driverLicenseSeria'  => null,
                                        'driverLicenseNumber' => null,
                                        'gender'              => $applicant['person']['gender']     ?? null,
                                        'birthDate'           => $applicant['person']['birth_date'] ?? null,
                                        'address'             => $applicant['person']['address']    ?? null,
                                        'residentType'        => (int) ($applicant['person']['resident_type'] ?? 1),
                                        'countryId'           => (string) ($applicant['person']['country_id']  ?? '210'),
                                        'phone'               => $applicant['person']['phone']      ?? null,
                                    ]
                                    : null,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::timeout(30)
            ->withBasicAuth(
                config('provider.username'),
                config('provider.password')
            )
            ->post(config('provider.submit.osgop'), $body);

        if (!$response->successful()) {
            Log::error('OSGOP Submit HTTP Error', [
                'body'     => $body,
                'response' => $response->body(),
            ]);
            throw new ProviderException('OSGOP submit service unavailable.');
        }

        $data = $response->json();

        if (($data['result'] ?? -1) !== 0) {
            Log::warning('OSGOP Submit Business Error', [
                'body'     => $body,
                'response' => $data,
            ]);
            throw new ProviderException($data['result_message'] ?? $data['message'] ?? 'OSGOP submit error.');
        }

        return $data;
    }

    private function osgopApplicantRegionId(array $applicant): string
    {
        if ($applicant['type'] === 'organization') {
            return (string) ($applicant['organization']['regionId'] ?? '10');
        }
        return (string) ($applicant['person']['region_id'] ?? '10');
    }

    private function buildOsgopInsurant(array $applicant): array
    {
        if ($applicant['type'] === 'organization') {
            $org = $applicant['organization'];
            return [
                'organization' => [
                    'inn'                => $org['inn']                ?? null,
                    'name'               => $org['name']               ?? null,
                    'representativeName' => $org['representativeName'] ?? null,
                    'address'            => $org['address']            ?? null,
                    'oked'               => $org['oked']               ?? null,
                    'position'           => $org['position']           ?? null,
                    'phone'              => $org['phone']              ?? null,
                    'regionId'           => (string) ($org['regionId'] ?? '10'),
                    'ownershipFormId'    => (string) ($org['ownershipFormId'] ?? '130'),
                ],
            ];
        }

        $p = $applicant['person'];
        return [
            'person' => [
                'passportData' => [
                    'pinfl'  => $p['pinfl']           ?? null,
                    'seria'  => $p['passport_seria']  ?? null,
                    'number' => $p['passport_number'] ?? null,
                ],
                'fullName' => [
                    'firstname'  => $p['firstname']  ?? null,
                    'lastname'   => $p['lastname']   ?? null,
                    'middlename' => $p['middlename'] ?? null,
                ],
                'regionId'            => (string) ($p['region_id'] ?? '10'),
                'driverLicenseSeria'  => null,
                'driverLicenseNumber' => null,
                'gender'              => $p['gender']      ?? null,
                'birthDate'           => $p['birth_date']  ?? null,
                'address'             => $p['address']     ?? null,
                'residentType'        => (int) ($p['resident_type'] ?? 1),
                'countryId'           => (string) ($p['country_id'] ?? '210'),
                'phone'               => $p['phone']       ?? null,
            ],
        ];
    }

    // =========================
    // OSGOR CALCULATE
    // =========================
    public function calculateOsgor(string $oked, float $fot): array
    {
        return $this->calcRequest(
            config('provider.calc.osgor'),
            [
                'insurant' => ['organization' => ['oked' => $oked]],
                'policies' => [['fot' => $fot]],
            ]
        );
    }

    // =========================
    // OSGOR SUBMIT
    // =========================
    public function submitOsgor(array $body): array
    {
        $response = Http::timeout(30)
            ->withBasicAuth(config('provider.username'), config('provider.password'))
            ->post(config('provider.submit.osgor'), $body);

        if (!$response->successful()) {
            Log::error('OSGOR Submit HTTP Error', ['body' => $body, 'response' => $response->body()]);
            throw new ProviderException('OSGOR submit service unavailable.');
        }

        $data = $response->json();

        if (($data['result'] ?? -1) !== 0) {
            Log::warning('OSGOR Submit Business Error', ['body' => $body, 'response' => $data]);
            throw new ProviderException($data['result_message'] ?? $data['message'] ?? 'OSGOR submit error.');
        }

        return $data;
    }

    // =========================
    // ACCIDENT CALCULATE
    // =========================
    public function calculateAccident(int $sumInsured): array
    {
        $url = 'http://online.xalqsugurta.uz/xs/ins/website/accident/calc';

        $response = Http::timeout(15)
            ->withBasicAuth(config('provider.username'), config('provider.password'))
            ->post($url, [
                'details' => ['productCode' => '202'],
                'persons' => [['sumInsured' => (string) $sumInsured]],
            ]);

        if (!$response->successful()) {
            Log::error('Accident Calc HTTP Error', [
                'sumInsured' => $sumInsured,
                'response'   => $response->body(),
            ]);
            throw new ProviderException('Accident calculation service unavailable.');
        }

        $data = $response->json();

        if (($data['result'] ?? -1) !== 0) {
            Log::warning('Accident Calc Business Error', [
                'sumInsured' => $sumInsured,
                'response'   => $data,
            ]);
            throw new ProviderException($data['result_message'] ?? 'Accident calculation error.');
        }

        return $data;
    }

    // =========================
    // TOURIST CALCULATE
    // =========================
    public function calculateTourist(int $sumInsured): array
    {
        $url = 'http://online.xalqsugurta.uz/xs/ins/website/accident/calc';

        $response = Http::timeout(15)
            ->withBasicAuth(config('provider.username'), config('provider.password'))
            ->post($url, [
                'details' => ['productCode' => '203'],
                'persons' => [['sumInsured' => (string) $sumInsured]],
            ]);

        if (!$response->successful()) {
            Log::error('Tourist Calc HTTP Error', [
                'sumInsured' => $sumInsured,
                'response'   => $response->body(),
            ]);
            throw new ProviderException('Tourist calculation service unavailable.');
        }

        $data = $response->json();

        if (($data['result'] ?? -1) !== 0) {
            Log::warning('Tourist Calc Business Error', [
                'sumInsured' => $sumInsured,
                'response'   => $data,
            ]);
            throw new ProviderException($data['result_message'] ?? 'Tourist calculation error.');
        }

        return $data;
    }

    // =========================
    // ACCIDENT SUBMIT
    // =========================
    public function submitAccident(array $body): array
    {
        $url = config('provider.submit.accident');

        $response = Http::timeout(30)
            ->withBasicAuth(config('provider.username'), config('provider.password'))
            ->post($url, $body);

        if (!$response->successful()) {
            Log::error('Accident Submit HTTP Error', ['body' => $body, 'response' => $response->body()]);
            throw new ProviderException('Accident submit service unavailable.');
        }

        $data = $response->json() ?? [];

        // Only check result if the API returns it (some endpoints return contract data directly)
        if (isset($data['result']) && $data['result'] !== 0) {
            Log::warning('Accident Submit Business Error', ['body' => $body, 'response' => $data]);
            throw new ProviderException($data['result_message'] ?? $data['message'] ?? 'Accident submit error.');
        }

        Log::info('Accident Submit successful', ['response' => $data]);

        return $data;
    }

    // =========================
    // XALQ SUGURTA UNIVERSAL SUBMIT (gas=35, property=36, kasko=37)
    // =========================
    public function submitXalqSugurta(array $body): array
    {
        $url = config('provider.xalq.base_url') . '/InitiateTransactionRequest';

        $response = Http::timeout(30)
            ->withBasicAuth(config('provider.xalq.username'), config('provider.xalq.password'))
            ->post($url, $body);

        if (!$response->successful()) {
            Log::error('Xalq Sugurta Submit HTTP Error', ['body' => $body, 'response' => $response->body()]);
            throw new ProviderException('Insurance submit service unavailable.');
        }

        $data = $response->json();

        $result = $data['result'] ?? null;
        if ($result !== null && $result !== 0 && $result !== 302) {
            Log::warning('Xalq Sugurta Submit Business Error', ['body' => $body, 'response' => $data]);
            throw new ProviderException($data['result_message'] ?? $data['message'] ?? 'Insurance submit error.');
        }

        return $data;
    }

    /** @deprecated Use submitXalqSugurta() */
    public function submitGasBallon(array $body): array
    {
        return $this->submitXalqSugurta($body);
    }

    // =========================
    // PROPERTY INSURANCE SUBMIT
    // =========================
    public function submitPropertyInsurance(array $data): array
    {
        return $this->providerRequest('POST', '/api/provider/property-insurance', $data);
    }

    // =========================
    // CADASTER
    // =========================
    public function findCadaster(string $cadasterNumber): array
    {
        return $this->providerRequest(
            'POST',
            '/api/provider/cadaster',
            [
                'cadasterNumber' => $cadasterNumber
            ]
        );
    }
}
