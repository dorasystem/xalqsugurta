<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\Osgop\OsgopStoreCompanyApplicant;
use App\Models\InsuranceTerm;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Carbon\Carbon;
use App\Services\Provider\ProviderApiTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class OsgopController extends Controller
{
    use ProviderApiTrait;

    private const SESSION_KEY = 'osgop';

    /**
     * The registry (osago/vehicle) and OSGOP number vehicle types differently: registry 1/2 are
     * passenger cars and 9 is a bus (> 20 seats); in the insurer's OSGOP table 1 is a bus and
     * 2 a passenger car. Sending the registry id priced cars as buses. Other types are not sold online.
     */
    private const OSGOP_TYPES = [1 => 2, 2 => 2, 9 => 1];

    // license.typeCode, as in the insurer's OSGOP samples
    private const LICENSE_TYPES = [
        1 => 'ЙЎЛОВЧИЛАРНИ ШАҲАРДА ШАҲАР АТРОФИДА ШАҲАРЛАРАРО МИКРОАВТОБУС ҲАМДА АВТОБУСЛАРДА ТАШИШ',
        2 => "YO'LOVCHILARNI SHAXAR, SHAXAR ATROFI VA SHAXARLARARO YENGIL AVTOMOBILLARDA TASHISH",
    ];

    public function __construct(private readonly OrderService $orderService) {}

    // ─── Step 1: Applicant (person or organization) ─────────────────────────

    public function index(): View
    {
        return view('pages.insurence.osgop.applicant', $this->flowViewData());
    }

    public function storeCompanyApplicant(OsgopStoreCompanyApplicant $request): RedirectResponse
    {
        try {
            $org = $this->findOrganizationByInn($request->input('inn'));
        } catch (ProviderException $e) {
            return back()
                ->withErrors(['inn' => $e->isUnavailable() ? __t('messages.flow.registry_unavailable') : __('messages.company_not_found')])
                ->withInput();
        }

        if (empty($org['name'] ?? null)) {
            return back()
                ->withErrors(['inn' => __('messages.company_not_found')])
                ->withInput();
        }

        session([self::SESSION_KEY . '.applicant' => [
            'type'         => 'organization',
            'organization' => [
                'inn'                => $request->input('inn'),
                'name'               => $org['name']               ?? '',
                'representativeName' => $org['gdFullName']          ?? $org['representativeName'] ?? '',
                'address'            => $org['address']             ?? '',
                'oked'               => $org['oked']                ?? '',
                'position'           => $org['position']            ?? 'Direktor',
                'phone'              => $this->cleanPhone($request->input('phone')),
                'regionId'           => $org['regionId']            ?? (isset($org['districtSoatoCode']) ? substr($org['districtSoatoCode'], 0, 2) : ''),
                'ownershipFormId'    => $org['ownershipFormId']     ?? '130',
            ],
        ]]);

        return redirect()->route('osgop.getVehicle', ['locale' => getCurrentLocale()]);
    }

    public function storeIndividualApplicant(Request $request): RedirectResponse
    {
        $request->merge(['phone' => $this->cleanPhone($request->input('phone'))]);

        $request->validate([
            'passport_seria'  => ['required', 'string', 'max:4'],
            'passport_number' => ['required', 'digits:7'],
            'pinfl'           => ['required', 'digits:14'],
            'phone'           => ['required', 'regex:/^998[0-9]{9}$/'],
        ]);

        $seria = strtoupper($request->input('passport_seria'));
        $pinfl = $request->input('pinfl');

        try {
            $person = $this->findPersonByPinfl($pinfl, $seria . $request->input('passport_number'));
        } catch (ProviderException $e) {
            return back()
                ->withErrors(['passport_seria' => $e->isUnavailable() ? __t('messages.flow.registry_unavailable') : __('messages.person_not_found')])
                ->withInput();
        }

        if (empty($person['lastNameLatin'] ?? $person['lastName'] ?? null)) {
            return back()
                ->withErrors(['passport_seria' => __('messages.person_not_found')])
                ->withInput();
        }

        session([self::SESSION_KEY . '.applicant' => [
            'type'   => 'person',
            'person' => [
                'passport_seria'  => $seria,
                'passport_number' => $request->input('passport_number'),
                'birth_date'      => $this->birthDate($person, $pinfl),
                'pinfl'           => $person['currentPinfl']    ?? $pinfl,
                'lastname'        => $person['lastNameLatin']   ?? $person['lastName']   ?? '',
                'firstname'       => $person['firstNameLatin']  ?? $person['firstName']  ?? '',
                'middlename'      => $person['middleNameLatin'] ?? $person['middleName'] ?? '',
                // API: 1 = male, 2 = female; the PINFL's first digit is odd for men
                'gender'          => ((string) ($person['gender'] ?? (int) $pinfl[0] % 2)) === '1' ? 'm' : 'f',
                'address'         => $person['address']         ?? '',
                'region_id'       => $person['regionId']        ?? '',
                'phone'           => $request->input('phone'),
                'resident_type'   => '1',
                'country_id'      => '210',
            ],
        ]]);

        return redirect()->route('osgop.getVehicle', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 2: Vehicle ──────────────────────────────────────────────────────

    public function getVehicle(): View|RedirectResponse
    {
        if (!session(self::SESSION_KEY . '.applicant')) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.osgop.vehicle', $this->flowViewData([
            'vehicleType' => $this->vehicleTypeLabel(session(self::SESSION_KEY . '.vehicle', [])),
        ]));
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $request->merge(['vehicle' => array_merge((array) $request->input('vehicle'), [
            'gov_number'          => strtoupper(preg_replace('/\s+/', '', (string) $request->input('vehicle.gov_number'))),
            'tech_passport_seria' => strtoupper(trim((string) $request->input('vehicle.tech_passport_seria'))),
            'license_seria'       => strtoupper(trim((string) $request->input('vehicle.license_seria'))),
        ])]);

        $request->validate([
            'vehicle.gov_number'           => ['required', 'string'],
            'vehicle.tech_passport_seria'  => ['required', 'string'],
            'vehicle.tech_passport_number' => ['required', 'string'],
            'vehicle.license_seria'        => ['required', 'regex:/^[A-Z]{2}$/'],
            'vehicle.license_number'       => ['required', 'digits:7'],
            'vehicle.license_begin'        => ['required', 'date', 'before_or_equal:today'],
            'vehicle.license_end'          => ['required', 'date', 'after_or_equal:today'],
        ], [
            'vehicle.license_seria.regex'         => __t('messages.flow.license_seria_format'),
            'vehicle.license_end.after_or_equal'  => __t('messages.flow.license_expired'),
        ]);

        $input   = $request->input('vehicle');
        $vehicle = [
            'gov_number'           => $input['gov_number'],
            'tech_passport_seria'  => $input['tech_passport_seria'],
            'tech_passport_number' => $input['tech_passport_number'],
        ];

        try {
            $api = $this->findVehicle(
                $vehicle['tech_passport_seria'],
                $vehicle['tech_passport_number'],
                $vehicle['gov_number']
            );

            // API ma'lumotlari bilan to'ldiriladi
            $vehicle['model_custom_name'] = $api['modelName']     ?? $api['modelCustomName'] ?? null;
            $vehicle['registry_type_id']  = (int) ($api['vehicleTypeId'] ?? 0);
            $vehicle['vehicle_type_id']   = self::OSGOP_TYPES[$vehicle['registry_type_id']] ?? null;
            $vehicle['issue_year']        = $api['issueYear']     ?? null;
            $vehicle['number_of_seats']   = $api['seats'] ?? null;
            $vehicle['body_number']       = $api['bodyNumber']    ?? null;
            $vehicle['engine_number']     = $api['engineNumber']  ?? null;
            $vehicle['region_id']         = $api['regionId']      ?? null;

            $vehicle['is_foreign']        = 0;

            // The registry has no carrier licence: the customer types it in
            $vehicle['license'] = [
                'seria'     => $input['license_seria'],
                'number'    => $input['license_number'],
                'beginDate' => Carbon::parse($input['license_begin'])->format('Y-m-d'),
                'endDate'   => Carbon::parse($input['license_end'])->format('Y-m-d'),
                'typeCode'  => self::LICENSE_TYPES[$vehicle['vehicle_type_id']] ?? null,
            ];
        } catch (ProviderException $e) {
            return back()
                ->withErrors(['vehicle.gov_number' => $e->isUnavailable() ? __t('messages.flow.registry_unavailable') : __('messages.vehicle_not_found')])
                ->withInput();
        }

        if (!session(self::SESSION_KEY . '.applicant')) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()]);
        }

        if (!$vehicle['vehicle_type_id']) {
            return back()
                ->withErrors(['vehicle.gov_number' => __t('messages.flow.osgop_type_unsupported')])
                ->withInput();
        }

        // A new vehicle invalidates the old premium
        session()->forget(self::SESSION_KEY . '.calculation');
        session([self::SESSION_KEY . '.vehicle' => $vehicle]);

        Log::info('OSGOP vehicle saved', ['gov' => $vehicle['gov_number']]);

        return redirect()->route('osgop.getCalculator', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 3: Calculator ───────────────────────────────────────────────────

    public function getCalculator(): View|RedirectResponse
    {
        if (!session(self::SESSION_KEY . '.applicant') || !session(self::SESSION_KEY . '.vehicle')) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.osgop.calculator', $this->flowViewData([
            'terms'       => InsuranceTerm::active()->orderBy('months')->get(),
            'vehicleType' => $this->vehicleTypeLabel(session(self::SESSION_KEY . '.vehicle', [])),
        ]));
    }

    /** AJAX: premium preview for the chosen term */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate($this->calculationRules());

        $vehicle = session(self::SESSION_KEY . '.vehicle');
        if (!$vehicle) {
            return response()->json(['success' => false, 'message' => __('messages.error_occurred')], 422);
        }

        try {
            $calculation = $this->calculation($vehicle, (int) $request->input('insurance_term_id'), $request->input('start_date'));
        } catch (ProviderException $e) {
            return response()->json(['success' => false, 'message' => $this->providerErrorMessage($e)], 422);
        }

        return response()->json(['success' => true, 'data' => $calculation]);
    }

    public function storeCalculation(Request $request): RedirectResponse
    {
        $vehicle = session(self::SESSION_KEY . '.vehicle');
        if (!session(self::SESSION_KEY . '.applicant') || !$vehicle) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()]);
        }

        $request->validate($this->calculationRules());

        try {
            $calculation = $this->calculation($vehicle, (int) $request->input('insurance_term_id'), $request->input('start_date'));
        } catch (ProviderException $e) {
            return back()->withErrors(['insurance_term_id' => $this->providerErrorMessage($e)])->withInput();
        }

        session([self::SESSION_KEY . '.calculation' => $calculation]);

        return redirect()->route('osgop.getConfirm', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 4: Confirm ──────────────────────────────────────────────────────

    public function getConfirm(): View|RedirectResponse
    {
        if (!session(self::SESSION_KEY . '.applicant') || !session(self::SESSION_KEY . '.vehicle') || !session(self::SESSION_KEY . '.calculation')) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.flow.confirm', $this->flowViewData([
            'product' => Product::where('route', self::SESSION_KEY)->first(),
        ]));
    }

    // ─── Store Application ────────────────────────────────────────────────────

    public function storeApplication(Request $request): RedirectResponse
    {
        $request->validate([
            'offerta_agreed' => ['required', 'accepted'],
        ], [
            'offerta_agreed.required' => __('messages.offerta_required'),
            'offerta_agreed.accepted' => __('messages.offerta_required'),
        ]);

        $applicant   = session(self::SESSION_KEY . '.applicant');
        $vehicle     = session(self::SESSION_KEY . '.vehicle');
        $calculation = session(self::SESSION_KEY . '.calculation');

        if (!$applicant || !$vehicle || !$calculation) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.error_occurred')]);
        }

        try {
            $apiResponse = $this->submitOsgop($applicant, $vehicle, $calculation);
        } catch (ProviderException $e) {
            return redirect()->route('osgop.getConfirm', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => $this->providerErrorMessage($e)]);
        }

        $insuranceId = $apiResponse['contract_id']
            ?? (($apiResponse['polis_sery'] ?? '') . ($apiResponse['polis_number'] ?? '') ?: null)
            ?? ($apiResponse['insurance_id'] ?? ($apiResponse['id'] ?? uniqid('osgop_')));

        $phone    = $applicant['organization']['phone'] ?? $applicant['person']['phone'] ?? null;
        $paymeUrl = $apiResponse['payme_url'] ?? null;
        $clickUrl = $apiResponse['click_url'] ?? null;

        $order = $this->orderService->createOrder([
            'product_name'             => __('insurance.osgop.product_name'),
            'amount'                   => $apiResponse['amount'] ?? $calculation['insurance_premium'],
            'insurance_id'             => (string) $insuranceId,
            'phone'                    => $phone,
            'insurances_data'          => [
                '_product_key' => self::SESSION_KEY,
                'applicant'    => $applicant,
                'vehicle'      => $vehicle,
                'calculation'  => $calculation,
            ],
            'insurances_response_data' => $apiResponse,
            'payme_url'                => $paymeUrl,
            'click_url'                => $clickUrl,
            'contractStartDate'        => $calculation['start_date'],
            'contractEndDate'          => $calculation['end_date'],
            'insuranceProductName'     => __('insurance.osgop.product_name'),
            'status'                   => Order::STATUS_NEW,
        ]);

        session()->forget(self::SESSION_KEY);

        return redirect()->route('payment.show', [
            'locale'  => getCurrentLocale(),
            'orderId' => $order->id,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function calculationRules(): array
    {
        return [
            'insurance_term_id' => ['required', 'integer', 'exists:insurance_terms,provider_term_id'],
            'start_date'        => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Premium from the insurer's calculator. Vehicle type and seats always come from the
     * vehicle found in the registry (session), never from the request: they set the price,
     * and submitOsgop() sends the same vehicle.
     */
    private function calculation(array $vehicle, int $termId, string $startDate): array
    {
        $typeId = (int) ($vehicle['vehicle_type_id'] ?? 0);
        $seats  = (int) ($vehicle['number_of_seats'] ?? 0);

        // Sessions from before the type mapping / licence step must go through the vehicle step again
        if (!in_array($typeId, self::OSGOP_TYPES, true) || $seats < 1 || empty($vehicle['registry_type_id']) || empty($vehicle['license']['number'])) {
            throw new ProviderException(__t('messages.flow.vehicle_incomplete'));
        }

        $result = $this->calculateOsgop($termId, $typeId, $seats);
        $months = InsuranceTerm::where('provider_term_id', $termId)->value('months') ?? 12;

        return [
            'insurance_term_id' => $termId,
            'start_date'        => Carbon::parse($startDate)->format('Y-m-d'),
            'end_date'          => Carbon::parse($startDate)->addMonths($months)->subDay()->format('Y-m-d'),
            'insurance_premium' => $result['insurancePremium'] ?? $result['premium'] ?? 0,
            'insurance_sum'     => $result['insuranceSum']     ?? $result['sumInsured'] ?? 0,
            'raw'               => $result,
        ];
    }

    /** Y-m-d from the API's birthDate, else from the PINFL (digits 2–7 = DDMMYY, first digit = century) */
    private function birthDate(array $person, string $pinfl): string
    {
        if (!empty($person['birthDate'])) {
            return Carbon::parse(str_replace('.', '-', $person['birthDate']))->format('Y-m-d');
        }

        $century = in_array($pinfl[0], ['1', '2'], true) ? 1800 : (in_array($pinfl[0], ['3', '4'], true) ? 1900 : 2000);

        return sprintf('%04d-%s-%s', $century + (int) substr($pinfl, 5, 2), substr($pinfl, 3, 2), substr($pinfl, 1, 2));
    }

    /** "Avtobus" / "Yengil avtomobil" for the OSGOP type in the session */
    private function vehicleTypeLabel(array $vehicle): ?string
    {
        return in_array($vehicle['vehicle_type_id'] ?? null, self::OSGOP_TYPES, true)
            ? __t('messages.flow.osgop_type_' . $vehicle['vehicle_type_id'])
            : null;
    }

    /** The insurer's own message, or "try later" when its service is down */
    private function providerErrorMessage(ProviderException $e): string
    {
        return $e->isUnavailable() ? __t('messages.flow.insurer_unavailable') : $e->getMessage();
    }

    /** Digits only, 998XXXXXXXXX when it can be; the caller validates the result */
    private function cleanPhone(?string $phone): string
    {
        $phone = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($phone, '00998')) {
            $phone = substr($phone, 2);
        }

        return strlen($phone) === 9 ? '998' . $phone : $phone;
    }

    /** View data in the unified flow shape (osgop views + pages/insurence/flow/confirm) */
    private function flowViewData(array $extra = []): array
    {
        $locale      = getCurrentLocale();
        $applicant   = session(self::SESSION_KEY . '.applicant');
        $vehicle     = session(self::SESSION_KEY . '.vehicle', []);
        $calculation = session(self::SESSION_KEY . '.calculation', []);

        $isOrg   = ($applicant['type'] ?? null) === 'organization';
        $who     = $isOrg ? ($applicant['organization'] ?? []) : ($applicant['person'] ?? []);
        $name    = $isOrg ? ($who['name'] ?? null) : (trim(($who['lastname'] ?? '') . ' ' . ($who['firstname'] ?? '') . ' ' . ($who['middlename'] ?? '')) ?: null);
        $car     = $vehicle ? trim(($vehicle['model_custom_name'] ?? '') . ', ' . ($vehicle['gov_number'] ?? ''), ', ') : null;
        $period  = !empty($calculation['start_date'])
            ? Carbon::parse($calculation['start_date'])->format('d.m.Y') . ' – ' . Carbon::parse($calculation['end_date'])->format('d.m.Y')
            : null;
        $premium = !empty($calculation['insurance_premium']) ? (int) round($calculation['insurance_premium']) : null;

        $vehicleUrl = route('osgop.getVehicle', ['locale' => $locale]);
        $calcUrl    = route('osgop.getCalculator', ['locale' => $locale]);

        return array_merge([
            'flow'            => ['key' => self::SESSION_KEY, 'icon' => 'bi-bus-front'],
            'applicant'       => $applicant,
            'vehicle'         => $vehicle,
            'calculation'     => $calculation,
            'premiumTotal'    => $premium,
            'flowSteps'       => [
                __t('messages.flow.applicant'),
                __t('messages.flow.vehicle'),
                __t('messages.flow.term'),
                __t('messages.confirm_details'),
                __t('messages.flow.payment'),
            ],
            'flowUrls'        => [route('osgop.index', ['locale' => $locale]), $vehicleUrl, $calcUrl, route('osgop.getConfirm', ['locale' => $locale])],
            'summaryItems'    => [
                'applicant' => [__t('messages.flow.applicant'), $name],
                'object'    => [__t('messages.flow.vehicle'), $car],
                'sum'       => [__('messages.insurance_sum'), !empty($calculation['insurance_sum']) ? formatMoney($calculation['insurance_sum']) : null],
                'period'    => [__t('messages.flow.period'), $period],
            ],
            'applicantTitle'  => $isOrg ? __t('messages.flow.organization') : __t('messages.flow.applicant'),
            'applicantReview' => $applicant ? ($isOrg ? [
                __t('messages.organization_name') => $who['name'] ?? null,
                __t('messages.inn')               => $who['inn'] ?? null,
                __('messages.phone_number')       => !empty($who['phone']) ? formatPhone($who['phone']) : null,
            ] : [
                __('messages.full_name')          => $name,
                __('insurance.passport.series') . ' / ' . __('insurance.passport.number') => ($who['passport_seria'] ?? '') . ' ' . ($who['passport_number'] ?? ''),
                __t('messages.flow.pinfl')        => $who['pinfl'] ?? null,
                __('messages.phone_number')       => !empty($who['phone']) ? formatPhone($who['phone']) : null,
            ]) : [],
            'confirmBlocks'   => [
                ['title' => __t('messages.flow.vehicle'), 'editUrl' => $vehicleUrl, 'items' => [
                    __('messages.gov_number')          => $vehicle['gov_number'] ?? null,
                    __t('messages.flow.vehicle')       => $vehicle['model_custom_name'] ?? null,
                    __('messages.tech_passport_series') . ' / ' . __('messages.tech_passport_number') => trim(($vehicle['tech_passport_seria'] ?? '') . ' ' . ($vehicle['tech_passport_number'] ?? '')) ?: null,
                    __t('messages.flow.vehicle_type')  => $this->vehicleTypeLabel($vehicle),
                    __t('messages.flow.seats')         => $vehicle['number_of_seats'] ?? null,
                    __t('messages.flow.license')       => !empty($vehicle['license']['number'])
                        ? $vehicle['license']['seria'] . ' ' . $vehicle['license']['number'] . ' (' . Carbon::parse($vehicle['license']['endDate'])->format('d.m.Y') . ' ' . __t('messages.flow.until') . ')'
                        : null,
                ]],
                ['title' => __t('messages.flow.policy_terms'), 'editUrl' => $calcUrl, 'items' => [
                    __('messages.insurance_sum')       => !empty($calculation['insurance_sum']) ? formatMoney($calculation['insurance_sum']) : null,
                    __t('messages.flow.period')        => $period,
                    __('messages.insurance_premium')   => $premium ? formatMoney($premium) : null,
                ]],
            ],
        ], $extra);
    }
}
