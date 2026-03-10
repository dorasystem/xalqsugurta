<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Services\OrderService;
use App\Services\PropertyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class GasBallonController extends BaseInsuranceController
{
    private const SESSION_KEY = 'gas';

    public function __construct(
        private readonly PropertyService $propertyService,
        OrderService $orderService
    ) {
        parent::__construct($orderService);
    }

    protected function getProductKey(): string
    {
        return self::SESSION_KEY;
    }

    // ─── Step 1: Applicant ────────────────────────────────────────────────────

    public function index(): View
    {
        return view('pages.insurence.gas.main', ['product' => $this->getProduct()]);
    }

    public function storeApplicant(Request $request): RedirectResponse
    {
        $request->validate([
            'passport_seria'  => ['required', 'string', 'max:4'],
            'passport_number' => ['required', 'digits:7'],
            'birth_date'      => ['required', 'date', 'before:today'],
            'phone'           => ['required', 'string', 'min:9', 'max:20'],
            'offerta_agreed'  => $this->offertaRule(),
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
            return back()->withErrors(['passport_seria' => __('messages.person_not_found')])->withInput();
        }

        if (empty($person['currentPinfl'] ?? null)) {
            return back()->withErrors(['passport_seria' => __('messages.person_not_found')])->withInput();
        }

        $this->putSess('applicant', array_merge($this->normalizePerson($person, $request), [
            'passport_seria'  => strtoupper($request->input('passport_seria')),
            'passport_number' => $request->input('passport_number'),
            'birth_date'      => $request->input('birth_date'),
            'phone'           => $this->cleanPhone($request->input('phone')),
            'gender'          => ($person['gender'] ?? '') == '1' ? '1' : '2',
        ]));

        return redirect()->route('gas.getProperty', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 2: Property ─────────────────────────────────────────────────────

    public function getProperty(): View|RedirectResponse
    {
        $applicant = $this->sess('applicant');
        if (!$applicant) {
            return redirect()->route('gas.index', ['locale' => getCurrentLocale()]);
        }

        $property    = $this->sess('property', []);
        $calculation = $this->sess('calculation', []);

        return view('pages.insurence.gas.property', compact('applicant', 'property', 'calculation'));
    }

    public function storeProperty(Request $request): RedirectResponse
    {
        $request->validate([
            'cadaster_number'    => ['required', 'string'],
            'insurance_amount'   => ['required', 'integer', 'min:5000000', 'max:500000000'],
            'payment_start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        if (!$this->sess('applicant')) {
            return redirect()->route('gas.index', ['locale' => getCurrentLocale()]);
        }

        $insuranceAmount = (int) $request->input('insurance_amount');
        $premium         = (int) round($insuranceAmount * 0.5 / 100);
        $startDate       = $request->input('payment_start_date');
        $endDate         = Carbon::parse($startDate)->addYear()->subDay()->format('Y-m-d');

        $districtId = (int) $request->input('prop_district_id', 0);
        $regionId   = $districtId > 0 ? (int) floor($districtId / 100) : 10;

        $this->putSess('property', [
            'cadasterNumber' => $request->input('cadaster_number'),
            'shortAddress'   => $request->input('short_address', ''),
            'objectArea'     => $request->input('object_area', ''),
            'tipText'        => $request->input('tip_text', ''),
            'vidText'        => $request->input('vid_text', ''),
            'region'         => $request->input('prop_region', ''),
            'regionId'       => $regionId,
            'districtId'     => $districtId,
            'district'       => $request->input('prop_district', ''),
        ]);

        $this->putSess('calculation', [
            'insurance_amount'   => $insuranceAmount,
            'insurance_premium'  => $premium,
            'payment_start_date' => $startDate,
            'payment_end_date'   => $endDate,
        ]);

        return redirect()->route('gas.getConfirm', ['locale' => getCurrentLocale()]);
    }

    // ─── AJAX: Cadaster ───────────────────────────────────────────────────────

    public function fetchCadaster(Request $request): JsonResponse
    {
        $request->validate([
            'cadasterNumber' => ['required', 'string'],
        ]);

        $result = $this->propertyService->fetchPropertyByCadaster($request->input('cadasterNumber'));

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['error'] ?? __('messages.cadaster_invalid')], 422);
        }

        return response()->json(['success' => true, 'result' => $result['result']]);
    }

    // ─── Step 3: Confirm + Submit ─────────────────────────────────────────────

    public function getConfirm(): View|RedirectResponse
    {
        $applicant   = $this->sess('applicant');
        $property    = $this->sess('property');
        $calculation = $this->sess('calculation');

        if (!$applicant || !$property || !$calculation) {
            return redirect()->route('gas.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.gas.confirm', compact('applicant', 'property', 'calculation'));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $applicant   = $this->sess('applicant');
        $property    = $this->sess('property');
        $calculation = $this->sess('calculation');

        if (!$applicant || !$property || !$calculation) {
            return redirect()->route('gas.index', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.error_occurred')]);
        }

        $apiBody = $this->buildGasApiBody($applicant, $property, $calculation);

        try {
            $apiResponse = $this->submitXalqSugurta($apiBody);
        } catch (ProviderException $e) {
            return redirect()->route('gas.getConfirm', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => $e->getMessage()]);
        }

        $insuranceId = null;
        if (isset($apiResponse['polis_sery'], $apiResponse['polis_number'])) {
            $insuranceId = $apiResponse['polis_sery'] . $apiResponse['polis_number'];
        }
        $insuranceId ??= $apiResponse['id'] ?? $apiResponse['UUID'] ?? uniqid('gas_');

        Log::info('Gas balloon order created', ['insurance_id' => $insuranceId]);

        return $this->createOrderAndRedirect([
            'product_name'             => __('insurance.gas.product_name'),
            'amount'                   => $calculation['insurance_premium'],
            'insurance_id'             => (string) $insuranceId,
            'phone'                    => $applicant['phone'],
            'insurances_data'          => [
                'applicant'             => $applicant,
                'property'              => $property,
                'calculation'           => $calculation,
                'xalq_contract_number'  => $apiBody['loan_info']['contract_number'] ?? null,
                'xalq_claim_id'         => $apiBody['loan_info']['claim_id'] ?? null,
            ],
            'insurances_response_data' => $apiResponse,
            'contractStartDate'        => $calculation['payment_start_date'],
            'contractEndDate'          => $calculation['payment_end_date'],
            'insuranceProductName'     => __('insurance.gas.product_name'),
        ], self::SESSION_KEY);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /** Convert Y-m-d to DD.MM.YYYY as required by Xalq Sugurta API */
    private function toApiDate(string $date): string
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y');
    }

    private function buildGasApiBody(array $applicant, array $property, array $calculation): array
    {
        $regionId         = (int) ($property['regionId'] ?? 10);
        $applicantFullName = trim($applicant['lastname'] . ' ' . $applicant['firstname'] . ' ' . ($applicant['middlename'] ?? ''));

        return [
            'customer' => [
                'address'    => $applicant['address'],
                'birth_date' => $this->toApiDate($applicant['birth_date']),
                'full_name'  => $applicantFullName,
                'gender'     => (int) $applicant['gender'],
                'passport'   => $applicant['passport_seria'] . $applicant['passport_number'],
                'phone'      => $applicant['phone'],
                'pinfl'      => $applicant['pinfl'],
            ],
            'loan_info' => [
                'cadastr_info' => [
                    'address'           => $property['shortAddress'] ?? $property['cadasterNumber'],
                    'cadastr_number'    => $property['cadasterNumber'],
                    'country'           => 210,
                    'description'       => $property['shortAddress'] ?? '',
                    'districtid'        => (int) ($property['districtId'] ?? 0),
                    'is_foreign'        => 0,
                    'is_owner'          => 1,
                    'name'              => $property['vidText'] ?? ($property['shortAddress'] ?? $property['cadasterNumber']),
                    'note'              => $property['tipText'] ?? '',
                    'region_code'       => (string) $regionId,
                    'regionid'          => $regionId,
                    'subject_full_name' => $applicantFullName,
                    'sum_bank'          => $calculation['insurance_amount'],
                ],
                'claim_id'        => uniqid('GAS-'),
                'contract_date'   => $this->toApiDate($calculation['payment_start_date']),
                'contract_number' => uniqid('GAS-'),
                'e_date'          => $this->toApiDate($calculation['payment_end_date']),
                'loan_type'       => config('provider.xalq.loan_type.gas', '35'),
                'object_name'     => $property['shortAddress'] ?? $property['cadasterNumber'],
                's_date'          => $this->toApiDate($calculation['payment_start_date']),
            ],
            'subject' => 'P',
        ];
    }
}
