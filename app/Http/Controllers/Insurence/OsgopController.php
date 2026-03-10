<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\Osgop\OsgopStoreCompanyApplicant;
use App\Models\InsuranceTerm;
use App\Models\Order;
use App\Models\Product;
use App\Models\VehicleType;
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

    public function __construct(private readonly OrderService $orderService) {}

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(): View
    {
        $product = Product::where('route', 'osgop')->first();
        return view('pages.insurence.osgop.index', compact('product'));
    }

    // ─── Applicant: Company ───────────────────────────────────────────────────

    public function storeCompanyApplicant(OsgopStoreCompanyApplicant $request): RedirectResponse
    {
        try {
            $org = $this->findOrganizationByInn($request->input('inn'));
        } catch (ProviderException $e) {
            return back()
                ->withErrors(['inn' => __('messages.company_not_found')])
                ->withInput();
        }

        if (empty($org['name'] ?? null)) {
            return back()
                ->withErrors(['inn' => __('messages.company_not_found')])
                ->withInput();
        }

        session([self::SESSION_KEY . '.applicant_raw' => [
            'type'         => 'organization',
            'organization' => [
                'inn'                => $request->input('inn'),
                'name'               => $org['name']               ?? '',
                'representativeName' => $org['gdFullName']          ?? $org['representativeName'] ?? '',
                'address'            => $org['address']             ?? '',
                'oked'               => $org['oked']                ?? '',
                'position'           => $org['position']            ?? 'Direktor',
                'phone'              => $this->cleanPhone($org['phone'] ?? ''),
                'regionId'           => $org['regionId']            ?? (isset($org['districtSoatoCode']) ? substr($org['districtSoatoCode'], 0, 2) : ''),
                'ownershipFormId'    => $org['ownershipFormId']     ?? '130',
            ],
        ]]);

        return redirect()->route('osgop.getVehicle', ['locale' => getCurrentLocale()]);
    }

    // ─── Applicant: Individual ────────────────────────────────────────────────

    public function storeIndividualApplicant(Request $request): RedirectResponse
    {
        $request->validate([
            'passport_seria'  => ['required', 'string', 'max:4'],
            'passport_number' => ['required', 'digits:7'],
            'birth_date'      => ['required', 'date', 'before:today'],
            'phone'           => ['required', 'string', 'min:9', 'max:20'],
            'offerta_agreed'  => ['required', 'accepted'],
        ], [
            'offerta_agreed.required' => __('messages.offerta_required'),
            'offerta_agreed.accepted' => __('messages.offerta_required'),
        ]);

        try {
            $person = $this->findPersonByPassport(
                strtoupper($request->input('passport_seria')) . $request->input('passport_number'),
                $request->input('birth_date')
            );
        } catch (ProviderException $e) {
            return back()
                ->withErrors(['passport_seria' => __('messages.person_not_found')])
                ->withInput();
        }

        session([self::SESSION_KEY . '.applicant_raw' => [
            'type'   => 'person',
            'person' => [
                'passport_seria'  => strtoupper($request->input('passport_seria')),
                'passport_number' => $request->input('passport_number'),
                'birth_date'      => $request->input('birth_date'),
                'pinfl'           => $person['currentPinfl']           ?? '',
                'lastname'        => $person['lastNameLatin']   ?? $person['lastName']   ?? '',
                'firstname'       => $person['firstNameLatin']  ?? $person['firstName']  ?? '',
                'middlename'      => $person['middleNameLatin'] ?? $person['middleName'] ?? '',
                'gender'          => ($person['gender']) == '1' ? 'm' : 'f', // API da 1 = erkak, 2 = ayol
                'address'         => $person['address']         ?? '',
                'region_id'       => $person['regionId']        ?? '',
                'phone'           => $this->cleanPhone($request->input('phone')),
                'resident_type'   => '1',
                'country_id'      => '210',
            ],
        ]]);

        return redirect()->route('osgop.getVehicle', ['locale' => getCurrentLocale()]);
    }

    // ─── Applicant: Confirm ───────────────────────────────────────────────────

    public function getApplicant(): View|RedirectResponse
    {
        $applicant = session(self::SESSION_KEY . '.applicant_raw');
        if (!$applicant) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.osgop.applicant_confirm', compact('applicant'));
    }

    public function confirmApplicant(): RedirectResponse
    {
        $applicant = session(self::SESSION_KEY . '.applicant_raw');
        if (!$applicant) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()]);
        }

        session([self::SESSION_KEY . '.applicant' => $applicant]);
        session()->forget(self::SESSION_KEY . '.applicant_raw');

        return redirect()->route('osgop.getVehicle', ['locale' => getCurrentLocale()]);
    }

    // ─── Vehicle ──────────────────────────────────────────────────────────────

    public function getVehicle(): View|RedirectResponse
    {
        $applicant = session(self::SESSION_KEY . '.applicant_raw')
            ?? session(self::SESSION_KEY . '.applicant');

        if (!$applicant) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()]);
        }

        $vehicle = session(self::SESSION_KEY . '.vehicle', []);

        return view('pages.insurence.osgop.vehicle', compact('vehicle', 'applicant'));
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle.gov_number'           => ['required', 'string'],
            'vehicle.tech_passport_seria'  => ['required', 'string'],
            'vehicle.tech_passport_number' => ['required', 'string'],
        ]);

        $vehicle = $request->input('vehicle');

        try {
            $api = $this->findVehicle(
                $vehicle['tech_passport_seria'],
                $vehicle['tech_passport_number'],
                $vehicle['gov_number']
            );

            // API ma'lumotlari bilan to'ldiriladi
            $vehicle['model_custom_name'] = $api['modelName']     ?? $api['modelCustomName'] ?? null;
            $vehicle['vehicle_type_id']   = $api['vehicleTypeId'] ?? null;
            $vehicle['issue_year']        = $api['issueYear']     ?? null;
            $vehicle['number_of_seats']   = $api['seats'] ?? null;
            $vehicle['body_number']       = $api['bodyNumber']    ?? null;
            $vehicle['engine_number']     = $api['engineNumber']  ?? null;
            $vehicle['region_id']         = $api['regionId']      ?? null;

            $vehicle['is_foreign']        = 0;

            $vehicle['license'] = [
                'seria'     => $api['licenseSeria']     ?? $api['licenseSerial']    ?? null,
                'number'    => $api['licenseNumber']    ?? $api['licenseNo']        ?? null,
                'beginDate' => $api['licenseBeginDate'] ?? $api['licenseStartDate'] ?? null,
                'endDate'   => $api['licenseEndDate']   ?? null,
                'typeCode'  => $api['licenseTypeCode']  ?? $api['vehicleCategory']  ?? null,
            ];
        } catch (ProviderException $e) {
            return back()
                ->withErrors(['vehicle.gov_number' => __('messages.vehicle_not_found')])
                ->withInput();
        }

        // Promote applicant_raw → applicant (company path skips confirmApplicant)
        if ($raw = session(self::SESSION_KEY . '.applicant_raw')) {
            session([self::SESSION_KEY . '.applicant' => $raw]);
            session()->forget(self::SESSION_KEY . '.applicant_raw');
        }

        session([self::SESSION_KEY . '.vehicle' => $vehicle]);

        Log::info('OSGOP vehicle saved', ['gov' => $vehicle['gov_number']]);

        return redirect()->route('osgop.getCalculator', ['locale' => getCurrentLocale()]);
    }

    // ─── Calculator ───────────────────────────────────────────────────────────

    public function getCalculator(): View|RedirectResponse
    {
        if (!session(self::SESSION_KEY . '.applicant') || !session(self::SESSION_KEY . '.vehicle')) {
            return redirect()->route('osgop.index', ['locale' => getCurrentLocale()]);
        }

        $applicant    = session(self::SESSION_KEY . '.applicant');
        $vehicle      = session(self::SESSION_KEY . '.vehicle');
        $terms        = InsuranceTerm::active()->orderBy('months')->get();
        $vehicleTypes = VehicleType::active()->orderBy('provider_vehicle_type_id')->get();

        return view('pages.insurence.osgop.calculator', compact('applicant', 'vehicle', 'terms', 'vehicleTypes'));
    }

    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'insurance_term_id' => ['required', 'integer', 'exists:insurance_terms,provider_term_id'],
            'vehicle_type_id'   => ['required', 'integer', 'exists:vehicle_types,provider_vehicle_type_id'],
            'number_of_seats'   => ['required', 'integer', 'min:1'],
            'start_date'        => ['required', 'date', 'after_or_equal:today'],
        ]);

        try {
            $result = $this->calculateOsgop(
                (int) $request->input('insurance_term_id'),
                (int) $request->input('vehicle_type_id'),
                (int) $request->input('number_of_seats'),
            );
        } catch (ProviderException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $term = InsuranceTerm::query()->where('provider_term_id', $request->input('insurance_term_id'))
            ->first();

        $startDate = $request->input('start_date');
        $endDate   = Carbon::parse($startDate)
            ->addMonths($term?->months ?? 12)
            ->subDay()
            ->format('Y-m-d');

        session([self::SESSION_KEY . '.calculation' => [
            'insurance_term_id'  => $request->input('insurance_term_id'),
            'start_date'         => $startDate,
            'end_date'           => $endDate,
            'insurance_premium'  => $result['insurancePremium'] ?? $result['premium'] ?? 0,
            'insurance_sum'      => $result['insuranceSum']     ?? $result['sumInsured'] ?? 0,
            'raw'                => $result,
        ]]);

        return response()->json(['success' => true, 'data' => $result]);
    }

    // ─── Store Application ────────────────────────────────────────────────────

    public function storeApplication(Request $request): RedirectResponse
    {
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
            return redirect()->route('osgop.getCalculator', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => $e->getMessage()]);
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

    private function cleanPhone(string $phone): string
    {
        // Faqat raqamlarni qoldiramiz
        $phone = preg_replace('/\D/', '', $phone);

        // 00998 bilan boshlangan bo‘lsa → 998 ga o‘tkazamiz
        if (str_starts_with($phone, '00998')) {
            $phone = substr($phone, 2);
        }

        // Agar 9 ta raqam bo‘lsa (masalan 901234567)
        if (strlen($phone) === 9) {
            $phone = '998' . $phone;
        }

        // Agar 998 bilan boshlanmasa va 12 ta bo‘lmasa — xato
        if (!str_starts_with($phone, '998') || strlen($phone) !== 12) {
            throw new \InvalidArgumentException('Telefon raqam noto‘g‘ri formatda.');
        }

        return $phone;
    }
}
