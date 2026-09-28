<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Services\InsuranceApiService;
use App\Services\OrderService;
use App\Services\OsagoPriceCalculator;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * OSAGO (compulsory motor liability): vehicle → owner + applicant → term + drivers → confirm.
 * Everything that sets the price (vehicle type, plate region, drivers) comes from the
 * registry lookups kept in the session, never from the request.
 */
final class OsagoController extends BaseInsuranceController
{
    private const SESSION_KEY = 'osago';

    public const MAX_DRIVERS = 5;

    // The only term on sale: 12 months (contractTermConclusionId 1, КС = 1)
    private const TERM_ID     = 1;
    private const TERM_PERIOD = '1';
    private const TERM_MONTHS = 12;

    private const SUM_INSURED = 80_000_000;

    public function __construct(
        OrderService $orderService,
        private readonly OsagoPriceCalculator $priceCalculator,
        private readonly InsuranceApiService $apiService,
    ) {
        parent::__construct($orderService);
    }

    protected function getProductKey(): string
    {
        return self::SESSION_KEY;
    }

    // ─── Step 1: Vehicle ──────────────────────────────────────────────────────

    public function index(): View
    {
        return view('pages.insurence.osago.vehicle', $this->flowViewData());
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $request->merge([
            'gov_number'           => strtoupper(preg_replace('/\s+/', '', (string) $request->input('gov_number'))),
            'tech_passport_seria'  => strtoupper(trim((string) $request->input('tech_passport_seria'))),
            'tech_passport_number' => preg_replace('/\s+/', '', (string) $request->input('tech_passport_number')),
        ]);

        $request->validate([
            'gov_number'           => ['required', 'regex:/^\d{2}[A-Z0-9]{5,7}$/'],
            'tech_passport_seria'  => ['required', 'regex:/^[A-Z]{3}$/'],
            'tech_passport_number' => ['required', 'digits:7'],
        ], [
            'gov_number.regex'          => __t('messages.flow.gov_number_format'),
            'tech_passport_seria.regex' => __t('messages.flow.tech_seria_format'),
        ]);

        $govNumber = $request->input('gov_number');

        try {
            $api = $this->findVehicle($request->input('tech_passport_seria'), $request->input('tech_passport_number'), $govNumber);
        } catch (ProviderException $e) {
            return back()->withErrors(['gov_number' => $this->lookupErrorMessage($e, __('messages.vehicle_not_found'))])->withInput();
        }

        if (empty($api['vehicleTypeId'])) {
            return back()->withErrors(['gov_number' => __t('messages.flow.vehicle_incomplete')])->withInput();
        }

        $vehicle = [
            'gov_number'               => $govNumber,
            'tech_passport_seria'      => $request->input('tech_passport_seria'),
            'tech_passport_number'     => $request->input('tech_passport_number'),
            'tech_passport_issue_date' => $this->apiDate($api['techPassportIssueDate'] ?? null),
            'model'                    => $api['modelName'] ?? $api['modelCustomName'] ?? '',
            'issue_year'               => (int) ($api['issueYear'] ?? 0),
            'type_id'                  => (int) $api['vehicleTypeId'],
            'body_number'              => (string) ($api['bodyNumber'] ?? ''),
            'engine_number'            => (string) ($api['engineNumber'] ?? ''),
            'owner_name'               => (string) ($api['owner'] ?? ''),
            'owner_pinfl'              => (string) ($api['pinfl'] ?? ''),
            'division'                 => (string) ($api['division'] ?? ''),
        ];

        // Another vehicle means another owner and another price
        if ($this->sess('vehicle.gov_number') !== $govNumber) {
            foreach (['owner', 'applicant', 'terms', 'drivers'] as $key) {
                session()->forget(self::SESSION_KEY . '.' . $key);
            }
        }

        $this->putSess('vehicle', $vehicle);

        Log::info('OSAGO vehicle found', ['type_id' => $vehicle['type_id']]);

        return redirect()->route('osago.getOwner', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 2: Owner + applicant ────────────────────────────────────────────

    public function getOwner(): View|RedirectResponse
    {
        if (!$this->sess('vehicle')) {
            return redirect()->route('osago.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.osago.owner', $this->flowViewData());
    }

    public function storeOwner(Request $request): RedirectResponse
    {
        if (!$this->sess('vehicle')) {
            return redirect()->route('osago.index', ['locale' => getCurrentLocale()]);
        }

        $request->merge(['phone' => $this->cleanPhone($request->input('phone'))]);
        $isOwner = $request->boolean('applicant_is_owner');

        $request->validate([
            'owner_seria'      => ['required', 'string', 'max:4'],
            'owner_number'     => ['required', 'digits:7'],
            'owner_pinfl'      => ['required', 'digits:14'],
            'applicant_seria'  => [$isOwner ? 'nullable' : 'required', 'string', 'max:4'],
            'applicant_number' => [$isOwner ? 'nullable' : 'required', 'digits:7'],
            'applicant_pinfl'  => [$isOwner ? 'nullable' : 'required', 'digits:14'],
            'phone'            => ['required', 'regex:/^998[0-9]{9}$/'],
            'email'            => ['nullable', 'email', 'max:100'],
        ]);

        try {
            $owner = $this->lookupOsagoPerson($this->passportInput($request, 'owner'));
        } catch (ProviderException $e) {
            return back()->withErrors(['owner_pinfl' => $this->lookupErrorMessage($e)])->withInput();
        }
        if (!$owner) {
            return back()->withErrors(['owner_pinfl' => __('messages.person_not_found')])->withInput();
        }

        $applicant = $owner;
        if (!$isOwner) {
            try {
                $applicant = $this->lookupOsagoPerson($this->passportInput($request, 'applicant'));
            } catch (ProviderException $e) {
                return back()->withErrors(['applicant_pinfl' => $this->lookupErrorMessage($e)])->withInput();
            }
            if (!$applicant) {
                return back()->withErrors(['applicant_pinfl' => __('messages.person_not_found')])->withInput();
            }
        }

        $applicant['phone']    = $request->input('phone');
        $applicant['email']    = (string) $request->input('email');
        $applicant['is_owner'] = $isOwner;

        $this->putSess('owner', $owner);
        $this->putSess('applicant', $applicant);

        return redirect()->route('osago.getTerms', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 3: Term + drivers ───────────────────────────────────────────────

    public function getTerms(): View|RedirectResponse
    {
        if (!$this->sess('vehicle') || !$this->sess('applicant')) {
            return redirect()->route('osago.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.osago.terms', $this->flowViewData([
            'premiums' => [
                'unlimited' => $this->premium('unlimited'),
                'limited'   => $this->premium('limited'),
            ],
        ]));
    }

    /** Find a driver (person + driving licence) and add them to the session list */
    public function addDriver(Request $request): RedirectResponse
    {
        if (!$this->sess('vehicle') || !$this->sess('applicant')) {
            return redirect()->route('osago.index', ['locale' => getCurrentLocale()]);
        }

        $request->merge(['driver_seria' => strtoupper(trim((string) $request->input('driver_seria')))]);
        $request->validate([
            'driver_seria'  => ['required', 'string', 'max:4'],
            'driver_number' => ['required', 'digits:7'],
            'driver_pinfl'  => ['required', 'digits:14'],
        ]);

        $back    = fn (string $message): RedirectResponse => redirect()
            ->route('osago.getTerms', ['locale' => getCurrentLocale()])
            ->withErrors(['driver_pinfl' => $message])
            ->withInput();
        $drivers = $this->sess('drivers', []);

        if (count($drivers) >= self::MAX_DRIVERS) {
            return $back(__t('messages.flow.drivers_max', ['max' => self::MAX_DRIVERS]));
        }
        if (collect($drivers)->contains('pinfl', $request->input('driver_pinfl'))) {
            return $back(__t('messages.flow.person_exists'));
        }

        try {
            $person = $this->lookupOsagoPerson([
                'passport_seria'  => $request->input('driver_seria'),
                'passport_number' => $request->input('driver_number'),
                'pinfl'           => $request->input('driver_pinfl'),
            ]);
        } catch (ProviderException $e) {
            return $back($this->lookupErrorMessage($e));
        }
        if (!$person) {
            return $back(__('messages.person_not_found'));
        }

        try {
            $summary = $this->findDriverLicense($person['pinfl'], $person['passport_seria'] . $person['passport_number']);
        } catch (ProviderException $e) {
            return $back($this->lookupErrorMessage($e, __t('messages.flow.driver_not_found')));
        }

        $license = $summary['DriverInfo'] ?? $summary['driverInfo'] ?? $summary;
        if (empty($license['licenseNumber'])) {
            return $back(__t('messages.flow.driver_not_found'));
        }

        $drivers[] = array_merge($person, [
            'license_seria'      => strtoupper(str_replace(' ', '', (string) ($license['licenseSeria'] ?? ''))),
            'license_number'     => str_replace(' ', '', (string) $license['licenseNumber']),
            'license_issue_date' => $this->apiDate($license['issueDate'] ?? null),
        ]);
        $this->putSess('drivers', $drivers);

        return redirect()->route('osago.getTerms', ['locale' => getCurrentLocale()]);
    }

    public function removeDriver(string $locale, int $index): RedirectResponse
    {
        $drivers = $this->sess('drivers', []);
        unset($drivers[$index]);
        $this->putSess('drivers', array_values($drivers));

        return redirect()->route('osago.getTerms', ['locale' => getCurrentLocale()]);
    }

    public function storeTerms(Request $request): RedirectResponse
    {
        if (!$this->sess('vehicle') || !$this->sess('applicant')) {
            return redirect()->route('osago.index', ['locale' => getCurrentLocale()]);
        }

        $request->validate([
            'start_date'   => ['required', 'date', 'after_or_equal:today'],
            'driver_limit' => ['required', 'in:unlimited,limited'],
        ]);

        $limit = $request->input('driver_limit');

        if ($limit === 'limited' && !$this->sess('drivers')) {
            return back()->withErrors(['drivers' => __t('messages.flow.drivers_required')])->withInput();
        }

        $start = Carbon::parse($request->input('start_date'));

        $this->putSess('terms', [
            'start_date'   => $start->format('Y-m-d'),
            'end_date'     => $start->copy()->addMonthsNoOverflow(self::TERM_MONTHS)->subDay()->format('Y-m-d'),
            'driver_limit' => $limit,
            'premium'      => $this->premium($limit),
        ]);

        return redirect()->route('osago.getConfirm', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 4: Confirm ──────────────────────────────────────────────────────

    public function getConfirm(): View|RedirectResponse
    {
        if (!$this->sess('vehicle') || !$this->sess('applicant') || !$this->sess('terms')) {
            return redirect()->route('osago.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.flow.confirm', $this->flowViewData([
            'product' => $this->getProduct(),
        ]));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $vehicle   = $this->sess('vehicle');
        $owner     = $this->sess('owner');
        $applicant = $this->sess('applicant');
        $terms     = $this->sess('terms');

        if (!$vehicle || !$owner || !$applicant || !$terms) {
            return redirect()->route('osago.index', ['locale' => getCurrentLocale()]);
        }

        $request->validate(['offerta_agreed' => $this->offertaRule()], [
            'offerta_agreed.required' => __('messages.offerta_required'),
            'offerta_agreed.accepted' => __('messages.offerta_required'),
        ]);

        $limited = $terms['driver_limit'] === 'limited';
        $drivers = $limited ? $this->sess('drivers', []) : [];

        if ($limited && !$drivers) {
            return redirect()->route('osago.getTerms', ['locale' => getCurrentLocale()])
                ->withErrors(['drivers' => __t('messages.flow.drivers_required')]);
        }

        // Recalculated here: the session premium was computed from the same registry data
        $premium = $this->premium($terms['driver_limit']);

        $result = $this->apiService->sendApplication($this->applicationBody($vehicle, $owner, $applicant, $terms, $drivers, $premium));

        if (!$result['success']) {
            Log::warning('OSAGO application rejected', ['error' => $result['error']]);

            return redirect()->route('osago.getConfirm', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => is_string($result['error']) && $result['error'] !== '' ? $result['error'] : __('messages.error_occurred')]);
        }

        $response = $result['data'];
        $uuid     = $response['UUID'] ?? $result['uuid'];

        Log::info('OSAGO order created', ['uuid' => $uuid]);

        return $this->createOrderAndRedirect([
            'product_name'             => __('insurance.osago.product_name'),
            'amount'                   => (int) ($response['amount'] ?? $premium),
            'insurance_id'             => (string) $uuid,
            'phone'                    => $applicant['phone'],
            'insurances_data'          => [
                'applicant' => $applicant,
                'owner'     => $owner,
                'vehicle'   => $vehicle,
                'terms'     => array_merge($terms, ['premium' => $premium]),
                'drivers'   => $drivers,
            ],
            'insurances_response_data' => $response,
            'payme_url'                => $response['payme_url'] ?? null,
            'click_url'                => $response['click_url'] ?? null,
            'contractStartDate'        => $terms['start_date'],
            'contractEndDate'          => $terms['end_date'],
            'insuranceProductName'     => __('insurance.osago.product_name'),
        ], self::SESSION_KEY);
    }

    /** Old payment links (/osago/payment/{order}) open the unified payment page */
    public function payment(string $locale, int $order): RedirectResponse
    {
        return redirect()->route('payment.show', ['locale' => $locale, 'orderId' => $order]);
    }

    // ─── Price ────────────────────────────────────────────────────────────────

    /** Premium for the vehicle in the session: type and plate region come from the registry */
    private function premium(string $driverLimit): int
    {
        $vehicle = $this->sess('vehicle');

        return $this->priceCalculator->calculate($vehicle['gov_number'], (int) $vehicle['type_id'], self::TERM_PERIOD, $driverLimit)['amount'];
    }

    // ─── Insurer's request body (doraosago/create) ────────────────────────────

    private function applicationBody(array $vehicle, array $owner, array $applicant, array $terms, array $drivers, int $premium): array
    {
        return [
            'vehicle' => [
                'govNumber'       => $vehicle['gov_number'],
                'engineNumber'    => $vehicle['engine_number'],
                'issueYear'       => $vehicle['issue_year'],
                'modelCustomName' => $vehicle['model'],
                'techPassport'    => [
                    'issueDate' => $vehicle['tech_passport_issue_date'],
                    'number'    => $vehicle['tech_passport_number'],
                    'seria'     => $vehicle['tech_passport_seria'],
                ],
                'regionId'        => $applicant['region_id'],
                'bodyNumber'      => $vehicle['body_number'],
                'terrainId'       => 2,
                'typeId'          => $vehicle['type_id'],
            ],
            'owner' => [
                'organization'     => ['inn' => null],
                'person'           => [
                    'passportData' => $this->passportData($owner),
                    'birthDate'    => $owner['birth_date'],
                    'fullName'     => $this->fullName($owner),
                ],
                'applicantIsOwner' => $applicant['is_owner'] ? 'true' : 'false',
            ],
            'applicant' => [
                'person' => [
                    'passportData' => $this->passportData($applicant),
                    'phoneNumber'  => $applicant['phone'],
                    'birthDate'    => $applicant['birth_date'],
                    'fullName'     => $this->fullName($applicant),
                    'gender'       => $applicant['gender'],
                    'districtId'   => $applicant['district_id'],
                    'regionId'     => $applicant['region_id'],
                ],
                'organization'  => ['phoneNumber' => '', 'inn' => '', 'name' => ''],
                'citizenshipId' => 1,
                'address'       => $applicant['address'],
                'email'         => $applicant['email'],
                'residentOfUzb' => 1,
            ],
            'details' => [
                'specialNote'             => '',
                'insuredActivityType'     => 'OSAGO',
                'issueDate'               => now()->format('Y-m-d'),
                'startDate'               => $terms['start_date'],
                'endDate'                 => $terms['end_date'],
                'driverNumberRestriction' => $terms['driver_limit'] === 'limited',
            ],
            'drivers' => array_map(fn (array $driver): array => [
                'passportData'     => $this->passportData($driver),
                'fullName'         => $this->fullName($driver),
                'licenseNumber'    => $driver['license_number'],
                'licenseSeria'     => $driver['license_seria'],
                'licenseIssueDate' => $driver['license_issue_date'],
                'birthDate'        => $driver['birth_date'],
                'residentOfUzb'    => 1,
            ], $drivers),
            'cost' => [
                'discountId'                    => 1,
                'sumInsured'                    => self::SUM_INSURED,
                'contractTermConclusionId'      => self::TERM_ID,
                'commission'                    => 0,
                'insurancePremium'              => $premium,
                'discountSum'                   => 0,
                'useTerritoryId'                => in_array(substr($vehicle['gov_number'], 0, 2), ['01', '10'], true) ? 1 : 2,
                'insurancePremiumPaidToInsurer' => $premium,
            ],
        ];
    }

    private function passportData(array $person): array
    {
        return [
            'pinfl'     => $person['pinfl'],
            'seria'     => $person['passport_seria'],
            'number'    => $person['passport_number'],
            'issuedBy'  => $person['passport_issued_by'],
            'issueDate' => $person['passport_issue_date'],
        ];
    }

    private function fullName(array $person): array
    {
        return [
            'firstname'  => $person['firstname'],
            'lastname'   => $person['lastname'],
            'middlename' => $person['middlename'],
        ];
    }

    // ─── Person lookup ────────────────────────────────────────────────────────

    /**
     * Person from the registry by passport + PINFL, in the shape the OSAGO body needs,
     * or null when not found (throws ProviderException when the registry is down). Passport issue data comes from the matching entry of
     * `documents` (pinfl-v2), else from the flat fields.
     */
    private function lookupOsagoPerson(array $input): ?array
    {
        $seria  = strtoupper(trim((string) ($input['passport_seria'] ?? '')));
        $number = (string) ($input['passport_number'] ?? '');
        $pinfl  = (string) ($input['pinfl'] ?? '');

        try {
            $person = $this->findPersonByPinfl($pinfl, $seria . $number);
        } catch (ProviderException $e) {
            // An outage is not "not found": the caller tells the user to try later
            if ($e->isUnavailable()) {
                throw $e;
            }

            return null;
        }

        $lastname = $person['lastNameLatin'] ?? $person['lastName'] ?? '';
        if ($lastname === '') {
            return null;
        }

        $person['currentPinfl'] ??= $pinfl;

        $document = collect($person['documents'] ?? [])->first(
            fn ($doc): bool => is_array($doc) && strtoupper((string) ($doc['document'] ?? '')) === $seria . $number
        ) ?? [];

        $request = new Request(['pinfl' => $pinfl]);

        return [
            'pinfl'               => (string) $person['currentPinfl'],
            'passport_seria'      => $seria,
            'passport_number'     => $number,
            'passport_issued_by'  => (string) ($document['docgiveplace'] ?? '') ?: $this->personField($person, ['docIssuedBy', 'issuedBy', 'passportIssuedBy', 'docGivePlace', 'givePlace']),
            'passport_issue_date' => $this->apiDate(($document['datebegin'] ?? null) ?: $this->personField($person, ['docIssueDate', 'issueDate', 'startDate', 'passportIssueDate', 'dateBegin', 'docGivenDate'])),
            'birth_date'          => $this->personBirthDate($person, $request),
            'lastname'            => $lastname,
            'firstname'           => $person['firstNameLatin'] ?? $person['firstName'] ?? '',
            'middlename'          => $person['middleNameLatin'] ?? $person['middleName'] ?? '',
            // OSAGO body: m / f
            'gender'              => $this->personGender($person) === '1' ? 'm' : 'f',
            'address'             => $this->personField($person, ['address', 'permanentAddress', 'fullAddress']),
            'region_id'           => (int) ($this->personField($person, ['regionId', 'region_id', 'regionCode']) ?: 10),
            'district_id'         => (int) $this->personField($person, ['districtId', 'district_id', 'districtCode']),
        ];
    }

    /** {prefix}_seria / _number / _pinfl from the request */
    private function passportInput(Request $request, string $prefix): array
    {
        return [
            'passport_seria'  => $request->input($prefix . '_seria'),
            'passport_number' => $request->input($prefix . '_number'),
            'pinfl'           => $request->input($prefix . '_pinfl'),
        ];
    }

    /** Y-m-d from the registry's date (ISO with time, Y-m-d or d.m.Y); '' when missing */
    private function apiDate(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        try {
            return Carbon::parse(str_replace('.', '-', substr($value, 0, 10)))->format('Y-m-d');
        } catch (\Carbon\Exceptions\InvalidFormatException) {
            return '';
        }
    }

    // ─── View data ────────────────────────────────────────────────────────────

    /** Drivers for the page: name + licence only */
    private function driverCards(): array
    {
        return array_map(fn (array $driver): array => [
            'pinfl'   => $driver['pinfl'],
            'name'    => trim($driver['lastname'] . ' ' . $driver['firstname'] . ' ' . $driver['middlename']),
            'license' => trim($driver['license_seria'] . ' ' . $driver['license_number']),
        ], $this->sess('drivers', []));
    }

    private function flowViewData(array $extra = []): array
    {
        $locale    = getCurrentLocale();
        $vehicle   = $this->sess('vehicle', []);
        $owner     = $this->sess('owner', []);
        $applicant = $this->sess('applicant', []);
        $terms     = $this->sess('terms', []);

        $name    = fn (array $p): ?string => $p ? trim($p['lastname'] . ' ' . $p['firstname'] . ' ' . $p['middlename']) : null;
        $car     = $vehicle ? trim($vehicle['model'] . ', ' . $vehicle['gov_number'], ', ') : null;
        $period  = $terms ? Carbon::parse($terms['start_date'])->format('d.m.Y') . ' – ' . Carbon::parse($terms['end_date'])->format('d.m.Y') : null;
        $premium = $terms['premium'] ?? null;
        $drivers = $this->driverCards();
        $limit   = $terms['driver_limit'] ?? null;

        $vehicleUrl = route('osago.index', ['locale' => $locale]);
        $ownerUrl   = route('osago.getOwner', ['locale' => $locale]);
        $termsUrl   = route('osago.getTerms', ['locale' => $locale]);

        $typeLabel = $vehicle ? __('insurance.car_type_' . ((int) $vehicle['type_id'] === 1 ? 2 : $vehicle['type_id'])) : null;
        if ($typeLabel && str_starts_with($typeLabel, 'insurance.')) {
            $typeLabel = null;
        }

        return array_merge([
            'flow'             => ['key' => self::SESSION_KEY, 'icon' => 'bi-car-front'],
            'vehicle'          => $vehicle,
            'vehicleType'      => $typeLabel,
            'owner'            => $owner,
            'applicant'        => $applicant,
            'terms'            => $terms,
            'drivers'          => $drivers,
            'maxDrivers'       => self::MAX_DRIVERS,
            'sumInsured'       => self::SUM_INSURED,
            'premiumTotal'     => $premium,
            'flowSteps'        => [
                __t('messages.flow.vehicle'),
                __t('messages.flow.owner'),
                __t('messages.flow.term'),
                __t('messages.confirm_details'),
                __t('messages.flow.payment'),
            ],
            'flowUrls'         => [$vehicleUrl, $ownerUrl, $termsUrl, route('osago.getConfirm', ['locale' => $locale])],
            'summaryItems'     => [
                'object'  => [__t('messages.flow.vehicle'), $car],
                'owner'   => [__t('messages.flow.owner'), $name($owner)],
                'sum'     => [__('messages.insurance_sum'), formatMoney(self::SUM_INSURED)],
                'period'  => [__t('messages.flow.period'), $period],
            ],
            'applicantTitle'   => __t('messages.flow.applicant'),
            'applicantEditUrl' => $ownerUrl,
            'applicantReview'  => $applicant ? [
                __('messages.full_name')    => $name($applicant),
                __t('messages.flow.pinfl')  => $applicant['pinfl'],
                __('messages.phone_number') => formatPhone($applicant['phone']),
                __('messages.email')        => $applicant['email'] ?: null,
            ] : [],
            'confirmBlocks'    => [
                ['title' => __t('messages.flow.vehicle'), 'editUrl' => $vehicleUrl, 'items' => [
                    __('messages.gov_number')             => $vehicle['gov_number'] ?? null,
                    __t('messages.flow.vehicle')          => $vehicle['model'] ?? null,
                    __t('messages.flow.vehicle_type')     => $typeLabel,
                    __('messages.tech_passport_series') . ' / ' . __('messages.tech_passport_number') => $vehicle ? $vehicle['tech_passport_seria'] . ' ' . $vehicle['tech_passport_number'] : null,
                ]],
                ['title' => __t('messages.flow.owner'), 'editUrl' => $ownerUrl, 'items' => [
                    __('messages.full_name')   => $name($owner),
                    __t('messages.flow.pinfl') => $owner['pinfl'] ?? null,
                ]],
                ['title' => __t('messages.flow.policy_terms'), 'editUrl' => $termsUrl, 'items' => [
                    __('messages.insurance_sum')     => formatMoney(self::SUM_INSURED),
                    __t('messages.flow.period')      => $period,
                    __t('messages.flow.drivers')     => $limit === 'limited'
                        ? implode(', ', array_column($drivers, 'name'))
                        : ($limit ? __t('messages.flow.drivers_unlimited') : null),
                    __('messages.insurance_premium') => $premium ? formatMoney($premium) : null,
                ]],
            ],
        ], $extra);
    }
}
