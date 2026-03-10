<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class KaskoController extends BaseInsuranceController
{
    private const SESSION_KEY = 'kasko';

    public function __construct(OrderService $orderService)
    {
        parent::__construct($orderService);
    }

    protected function getProductKey(): string
    {
        return self::SESSION_KEY;
    }

    // ─── Step 1: Applicant ────────────────────────────────────────────────────

    public function index(): View
    {
        return view('pages.insurence.kasko.main', ['product' => $this->getProduct()]);
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

        return redirect()->route('kasko.getVehicle', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 2: Vehicle + Calculation ───────────────────────────────────────

    public function getVehicle(): View|RedirectResponse
    {
        $applicant = $this->sess('applicant');
        if (!$applicant) {
            return redirect()->route('kasko.index', ['locale' => getCurrentLocale()]);
        }

        $vehicle     = $this->sess('vehicle', []);
        $calculation = $this->sess('calculation', []);

        return view('pages.insurence.kasko.vehicle', compact('applicant', 'vehicle', 'calculation'));
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $request->validate([
            'regnumber'          => ['required', 'string'],
            'tp_seria'           => ['required', 'string', 'max:3'],
            'tp_number'          => ['required', 'string', 'max:7'],
            'brand'              => ['required', 'string'],
            'model'              => ['required', 'string'],
            'year'               => ['required', 'integer', 'min:1900', 'max:' . date('Y')],
            'body_number'        => ['required', 'string'],
            'engine_number'      => ['required', 'string'],
            'vehicle_type'       => ['required', 'integer'],
            'insurance_amount'   => ['required', 'integer', 'min:1000000'],
            'payment_start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        if (!$this->sess('applicant')) {
            return redirect()->route('kasko.index', ['locale' => getCurrentLocale()]);
        }

        $insuranceAmount = (int) $request->input('insurance_amount');
        $premium         = (int) round($insuranceAmount * 3 / 100);
        $startDate       = $request->input('payment_start_date');
        $endDate         = Carbon::parse($startDate)->addYear()->subDay()->format('Y-m-d');

        $this->putSess('vehicle', [
            'regnumber'     => strtoupper($request->input('regnumber')),
            'tp_seria'      => strtoupper($request->input('tp_seria')),
            'tp_number'     => $request->input('tp_number'),
            'brand'         => strtoupper($request->input('brand')),
            'model'         => $request->input('model'),
            'year'          => (int) $request->input('year'),
            'body_number'   => $request->input('body_number'),
            'engine_number' => $request->input('engine_number'),
            'vehicle_type'  => (int) $request->input('vehicle_type'),
        ]);

        $this->putSess('calculation', [
            'insurance_amount'   => $insuranceAmount,
            'insurance_premium'  => $premium,
            'payment_start_date' => $startDate,
            'payment_end_date'   => $endDate,
        ]);

        return redirect()->route('kasko.getConfirm', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 3: Confirm + Submit ─────────────────────────────────────────────

    public function getConfirm(): View|RedirectResponse
    {
        $applicant   = $this->sess('applicant');
        $vehicle     = $this->sess('vehicle');
        $calculation = $this->sess('calculation');

        if (!$applicant || !$vehicle || !$calculation) {
            return redirect()->route('kasko.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.kasko.confirm', compact('applicant', 'vehicle', 'calculation'));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $applicant   = $this->sess('applicant');
        $vehicle     = $this->sess('vehicle');
        $calculation = $this->sess('calculation');

        if (!$applicant || !$vehicle || !$calculation) {
            return redirect()->route('kasko.index', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.error_occurred')]);
        }

        $apiBody = $this->buildKaskoApiBody($applicant, $vehicle, $calculation);

        try {
            $apiResponse = $this->submitXalqSugurta($apiBody);
        } catch (ProviderException $e) {
            return redirect()->route('kasko.getConfirm', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => $e->getMessage()]);
        }

        $insuranceId = $apiResponse['contract_id']
            ?? $apiResponse['polis_sery'] . ($apiResponse['polis_number'] ?? '')
            ?: ($apiResponse['id'] ?? uniqid('kasko_'));

        $paymeUrl = $apiResponse['payme_url'] ?? null;
        $clickUrl = $apiResponse['click_url'] ?? null;

        Log::info('Kasko order created', ['insurance_id' => $insuranceId]);

        return $this->createOrderAndRedirect([
            'product_name'             => __('insurance.kasko.product_name'),
            'amount'                   => $apiResponse['amount'] ?? $calculation['insurance_premium'],
            'insurance_id'             => (string) $insuranceId,
            'phone'                    => $applicant['phone'],
            'insurances_data'          => [
                'applicant'            => $applicant,
                'vehicle'              => $vehicle,
                'calculation'          => $calculation,
                'xalq_contract_number' => $apiBody['loan_info']['contract_number'] ?? null,
                'xalq_claim_id'        => $apiBody['loan_info']['claim_id'] ?? null,
            ],
            'insurances_response_data' => $apiResponse,
            'payme_url'                => $paymeUrl,
            'click_url'                => $clickUrl,
            'contractStartDate'        => $calculation['payment_start_date'],
            'contractEndDate'          => $calculation['payment_end_date'],
            'insuranceProductName'     => __('insurance.kasko.product_name'),
        ], self::SESSION_KEY);
    }

    // ─── AJAX: Find Vehicle ───────────────────────────────────────────────────

    public function findVehicleAjax(Request $request): JsonResponse
    {
        $request->validate([
            'gov_number' => ['required', 'string'],
            'tp_seria'   => ['required', 'string'],
            'tp_number'  => ['required', 'string'],
        ]);

        try {
            $api = $this->findVehicle(
                strtoupper($request->input('tp_seria')),
                $request->input('tp_number'),
                strtoupper($request->input('gov_number'))
            );
        } catch (ProviderException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'regnumber'     => strtoupper($request->input('gov_number')),
                'tp_seria'      => strtoupper($request->input('tp_seria')),
                'tp_number'     => $request->input('tp_number'),
                'brand'         => $api['markName']        ?? '',
                'model'         => $api['modelCustomName'] ?? $api['modelName'] ?? '',
                'year'          => $api['issueYear']       ?? '',
                'body_number'   => $api['bodyNumber']      ?? '',
                'engine_number' => $api['engineNumber']    ?? '',
                'vehicle_type'  => $api['vehicleTypeId']   ?? 2,
            ],
        ]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function toApiDate(string $date): string
    {
        return Carbon::parse($date)->format('d.m.Y');
    }

    private function buildKaskoApiBody(array $applicant, array $vehicle, array $calculation): array
    {
        $fullName   = trim($applicant['lastname'] . ' ' . $applicant['firstname'] . ' ' . ($applicant['middlename'] ?? ''));
        $brand      = $vehicle['brand'] ?? '';
        $model      = $vehicle['model'] ?? '';
        $objectName = $brand === $model ? $model : trim($brand . ' ' . $model);

        $customer = [
            'address'    => $applicant['address'],
            'birth_date' => $this->toApiDate($applicant['birth_date']),
            'full_name'  => $fullName,
            'gender'     => (int) $applicant['gender'],
            'passport'   => $applicant['passport_seria'] . $applicant['passport_number'],
            'phone'      => $applicant['phone'],
            'pinfl'      => $applicant['pinfl'],
        ];

        return [
            'customer'     => $customer,
            'loan_info'    => [
                'claim_id'        => uniqid('KASKO-'),
                'contract_date'   => $this->toApiDate($calculation['payment_start_date']),
                'contract_number' => uniqid('KASKO-'),
                'e_date'          => $this->toApiDate($calculation['payment_end_date']),
                'loan_amount'     => $calculation['insurance_amount'],
                'loan_type'       => config('provider.xalq.loan_type.kasko', '37'),
                'object_brand'    => $brand,
                'object_name'     => $objectName,
                's_date'          => $this->toApiDate($calculation['payment_start_date']),
            ],
            // Vehicle owner (same person as customer for individual kasko)
            'organization' => array_merge($customer, ['subject' => 'P']),
            'subject'      => 'P',
            'vehicle_info' => [
                'bodynumber'   => $vehicle['body_number'],
                'enginenumber' => $vehicle['engine_number'],
                'regnumber'    => $vehicle['regnumber'],
                'techpassport' => [
                    'number' => $vehicle['tp_number'],
                    'seria'  => $vehicle['tp_seria'],
                ],
                'type' => $vehicle['vehicle_type'],
                'year' => $vehicle['year'],
            ],
        ];
    }
}
