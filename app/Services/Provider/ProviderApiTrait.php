<?php

namespace App\Services\Provider;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ProviderException;

trait ProviderApiTrait
{
    protected function providerRequest(string $method, string $param, array $body = []): array
    {
        // Bodies hold passport data and are kept in the admin "API jurnali" (ApiLogger), not in the app log
        try {
            $response = Http::timeout(10)
                ->retry(3, 500, throw: false)
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
        } catch (ConnectionException $e) {
            Log::error('Provider connection error', ['param' => $param, 'message' => $e->getMessage()]);

            throw new ProviderException('Provider service unavailable.', 503, $e);
        }

        if (!$response->successful()) {
            Log::error('Provider HTTP Error', ['param' => $param, 'status' => $response->status()]);

            throw new ProviderException('Provider service unavailable.', 503);
        }

        $data = $response->json();

        if (isset($data['error']) && $data['error'] != 0) {
            Log::warning('Provider Business Error', [
                'param' => $param,
                'error' => $data['error'],
                'error_message' => $data['error_message'] ?? null,
            ]);

            // The provider's own code: 503 = the registry behind it is down
            throw new ProviderException(
                $data['error_message'] ?? 'Provider business error.',
                is_numeric($data['error']) ? (int) $data['error'] : 0
            );
        }

        return $data['result'] ?? $data;
    }

    /**
     * POST to one of the insurer's own endpoints (calculators, sales). A timeout or a 5xx
     * becomes a ProviderException with code 503 (isUnavailable()); another HTTP error carries
     * the API's result_message and its status code. Bodies stay out of the app log: they are
     * in the admin "API jurnali".
     */
    protected function insurerPost(string $url, array $body, int $timeout = 30, int $retries = 1, ?array $auth = null): Response
    {
        [$user, $password] = $auth ?? [config('provider.username'), config('provider.password')];

        try {
            $response = Http::timeout($timeout)
                ->retry($retries, 500, throw: false)
                ->withBasicAuth((string) $user, (string) $password)
                ->post($url, $body);
        } catch (ConnectionException $e) {
            Log::error('Insurer connection error', ['url' => $url, 'message' => $e->getMessage()]);

            throw new ProviderException('Insurer service unavailable.', 503, $e);
        }

        if (!$response->successful()) {
            Log::error('Insurer HTTP error', ['url' => $url, 'status' => $response->status()]);

            if ($response->serverError()) {
                throw new ProviderException('Insurer service unavailable.', 503);
            }

            $data = $response->json() ?? [];

            throw new ProviderException(
                $data['result_message'] ?? $data['message'] ?? __('messages.error_occurred') . ' (HTTP ' . $response->status() . ')',
                $response->status()
            );
        }

        return $response;
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
    // DRIVER LICENSE (OSAGO limited drivers)
    // =========================
    public function findDriverLicense(string $pinfl, string $document): array
    {
        return $this->providerRequest(
            'POST',
            '/api/provider/driver-summary-v2',
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
    // CALCULATE (direct URL, extensible per product)
    // =========================
    protected function calcRequest(string $url, array $body): array
    {
        $response = $this->insurerPost($url, $body, 15, retries: 3);

        $data = $response->json();

        // result: 0 = success, non-zero = business error
        if (($data['result'] ?? -1) !== 0) {
            Log::warning('Calc Business Error', [
                'url'      => $url,
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

        // The insurer's samples carry only the owner block that applies; a null one is left out
        $body['policies'][0]['objects'][0]['vehicle'] = array_filter(
            $body['policies'][0]['objects'][0]['vehicle'],
            fn ($value, string $key): bool => $value !== null || !in_array($key, ['ownerOrganization', 'ownerPerson'], true),
            ARRAY_FILTER_USE_BOTH
        );

        $response = $this->insurerPost(config('provider.submit.osgop'), $body);

        $data = $response->json();

        if (($data['result'] ?? -1) !== 0) {
            Log::warning('OSGOP Submit Business Error', ['response' => $data]);
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
        $response = $this->insurerPost(config('provider.submit.osgor'), $body);

        $data = $response->json();

        if (($data['result'] ?? -1) !== 0) {
            Log::warning('OSGOR Submit Business Error', ['response' => $data]);
            throw new ProviderException($data['result_message'] ?? $data['message'] ?? 'OSGOR submit error.');
        }

        return $data;
    }

    // =========================
    // ACCIDENT / TOURIST CALCULATE (website/accident/calc)
    // =========================
    /**
     * Premium for one person of an accident-type product ('202' accident, '203' tourist).
     * The calculator rejects requests without a policy period ("Ошибка даты начало страхования"):
     * $startDate is Y-m-d (defaults to tomorrow), $termMonths comes from the product settings.
     */
    public function calculatePersonsInsurance(string $productCode, int $sumInsured, ?string $startDate = null, int $termMonths = 12): array
    {
        $start = \Carbon\Carbon::parse($startDate ?? now()->addDay())->startOfDay();

        $url = 'http://online.xalqsugurta.uz/xs/ins/website/accident/calc';

        $response = $this->insurerPost($url, [
            'details' => [
                'productCode' => $productCode,
                'startDate'   => $start->format('Y-m-d'),
                'endDate'     => $start->copy()->addMonths($termMonths)->subDay()->format('Y-m-d'),
            ],
            'persons' => [['sumInsured' => (string) $sumInsured]],
        ], 15);

        $data = $response->json();

        if (($data['result'] ?? -1) !== 0) {
            Log::warning('Accident Calc Business Error', [
                'productCode' => $productCode,
                'sumInsured'  => $sumInsured,
                'response'    => $data,
            ]);
            throw new ProviderException($data['result_message'] ?? 'Accident calculation error.');
        }

        return $data;
    }

    // =========================
    // ACCIDENT SUBMIT
    // =========================
    /** Sale of an accident-type product; $url defaults to provider.submit.accident (tourist has its own key) */
    public function submitAccident(array $body, ?string $url = null): array
    {
        $url ??= config('provider.submit.accident');

        // A rejected sale usually explains itself in result_message; insurerPost() shows that
        $response = $this->insurerPost($url, $body);

        $data = $response->json() ?? [];

        // Only check result if the API returns it (some endpoints return contract data directly)
        if (isset($data['result']) && $data['result'] !== 0) {
            Log::warning('Accident Submit Business Error', ['response' => $data]);
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

        $response = $this->insurerPost($url, $body, auth: [config('provider.xalq.username'), config('provider.xalq.password')]);

        $data = $response->json();

        $result = $data['result'] ?? null;
        if ($result !== null && $result !== 0 && $result !== 302) {
            Log::warning('Xalq Sugurta Submit Business Error', ['response' => $data]);
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
