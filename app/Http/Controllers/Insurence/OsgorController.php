<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class OsgorController extends BaseInsuranceController
{
    private const SESSION_KEY = 'osgor';

    public function __construct(OrderService $orderService)
    {
        parent::__construct($orderService);
    }

    protected function getProductKey(): string
    {
        return self::SESSION_KEY;
    }

    // ─── Step 1: INN → Organization ──────────────────────────────────────────

    public function index(): View
    {
        return view('pages.insurence.osgor.main', ['product' => $this->getProduct()]);
    }

    public function storeApplicant(Request $request): RedirectResponse
    {
        $request->validate([
            'inn'            => ['required', 'digits:9'],
            'offerta_agreed' => $this->offertaRule(),
        ], [
            'inn.required'            => __('messages.inn_required'),
            'inn.digits'              => __('messages.inn_invalid'),
            'offerta_agreed.required' => __('messages.offerta_required'),
            'offerta_agreed.accepted' => __('messages.offerta_required'),
        ]);

        try {
            $org = $this->findOrganizationByInn($request->input('inn'));
        } catch (ProviderException $e) {
            return back()->withErrors(['inn' => __('messages.company_not_found')])->withInput();
        }

        if (empty($org['name'] ?? null)) {
            return back()->withErrors(['inn' => __('messages.company_not_found')])->withInput();
        }

        $this->putSess('applicant', [
            'inn'                => $request->input('inn'),
            'name'               => $org['name']               ?? '',
            'representativeName' => $org['gdFullName']          ?? $org['representativeName'] ?? '',
            'address'            => $org['address']             ?? '',
            'oked'               => $org['oked']                ?? '',
            'position'           => $org['position']            ?? 'Direktor',
            'phone'              => $this->cleanPhone($org['phone'] ?? ''),
            'regionId'           => $org['regionId']            ?? (isset($org['districtSoatoCode']) ? substr($org['districtSoatoCode'], 0, 2) : '10'),
            'ownershipFormId'    => $org['ownershipFormId']     ?? '130',
        ]);

        return redirect()->route('osgor.getCalculator', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 2: Calculator ───────────────────────────────────────────────────

    public function getCalculator(): View|RedirectResponse
    {
        $applicant = $this->sess('applicant');
        if (!$applicant) {
            return redirect()->route('osgor.index', ['locale' => getCurrentLocale()]);
        }

        $calculation = $this->sess('calculation', []);

        return view('pages.insurence.osgor.calculator', compact('applicant', 'calculation'));
    }

    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'fot'        => ['required', 'numeric', 'min:1'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $applicant = $this->sess('applicant');
        if (!$applicant) {
            return response()->json(['success' => false, 'message' => __('messages.error_occurred')], 422);
        }

        try {
            $result = $this->calculateOsgor($applicant['oked'], (float) $request->input('fot'));
        } catch (ProviderException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $startDate = $request->input('start_date');
        $endDate   = Carbon::parse($startDate)->addYear()->subDay()->format('Y-m-d');

        return response()->json([
            'success' => true,
            'data'    => [
                'insurance_premium'   => $result['insurancePremium']  ?? $result['premium']          ?? 0,
                'insurance_sum'       => $result['insuranceSum']       ?? $result['sumInsured']       ?? 0,
                'insurance_rate'      => $result['insuranceRate']      ?? $result['rate']             ?? 0,
                'funeral_expenses_sum' => $result['funeralExpensesSum'] ?? 0,
                'insurance_term_id'   => $result['insuranceTermId']    ?? 4,
                'start_date'          => $startDate,
                'end_date'            => $endDate,
                'raw'                 => $result,
            ],
        ]);
    }

    public function storeCalculation(Request $request): RedirectResponse
    {
        $request->validate([
            'fot'                 => ['required', 'numeric', 'min:1'],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['required', 'date'],
            'insurance_premium'   => ['required', 'numeric'],
            'insurance_sum'       => ['required', 'numeric'],
            'insurance_rate'      => ['required', 'numeric'],
            'funeral_expenses_sum' => ['required', 'numeric'],
            'insurance_term_id'   => ['required', 'integer'],
        ]);

        $this->putSess('calculation', [
            'fot'                 => (float) $request->input('fot'),
            'start_date'          => $request->input('start_date'),
            'end_date'            => $request->input('end_date'),
            'insurance_premium'   => (float) $request->input('insurance_premium'),
            'insurance_sum'       => (float) $request->input('insurance_sum'),
            'insurance_rate'      => (float) $request->input('insurance_rate'),
            'funeral_expenses_sum' => (float) $request->input('funeral_expenses_sum'),
            'insurance_term_id'   => (int)   $request->input('insurance_term_id'),
        ]);

        return redirect()->route('osgor.getConfirm', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 3: Confirm + Submit ─────────────────────────────────────────────

    public function getConfirm(): View|RedirectResponse
    {
        $applicant   = $this->sess('applicant');
        $calculation = $this->sess('calculation');

        if (!$applicant || !$calculation) {
            return redirect()->route('osgor.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.osgor.confirm', compact('applicant', 'calculation'));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $applicant   = $this->sess('applicant');
        $calculation = $this->sess('calculation');

        if (!$applicant || !$calculation) {
            return redirect()->route('osgor.index', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.error_occurred')]);
        }

        // API requires insuranceSum >= insurancePremium.
        // funeralExpensesSum is the actual total coverage; insurance_sum (FOT) is just the salary fund.
        $effectiveSum = max(
            (float) $calculation['funeral_expenses_sum'],
            (float) $calculation['insurance_sum'],
            (float) $calculation['insurance_premium'],
        );

        $body = [
            'number'            => date('dmy') . '-' . now()->timestamp,
            'sum'               => (string) $effectiveSum,
            'contractStartDate' => $calculation['start_date'],
            'contractEndDate'   => $calculation['end_date'],
            'regionId'          => (string) $applicant['regionId'],
            'areaTypeId'        => '1',
            'agencyId'          => config('provider.agency_id'),
            'comission'         => '0',
            'insurant'          => [
                'organization' => [
                    'inn'                => $applicant['inn'],
                    'name'               => $applicant['name'],
                    'representativeName' => $applicant['representativeName'],
                    'address'            => $applicant['address'],
                    'oked'               => $applicant['oked'],
                    'position'           => $applicant['position'],
                    'phone'              => $applicant['phone'],
                    'regionId'           => (string) $applicant['regionId'],
                    'ownershipFormId'    => (string) $applicant['ownershipFormId'],
                ],
            ],
            'policies' => [
                [
                    'startDate'          => $calculation['start_date'],
                    'endDate'            => $calculation['end_date'],
                    'insuranceSum'       => (string) $effectiveSum,
                    'insuranceRate'      => (string) $calculation['insurance_rate'],
                    'insurancePremium'   => (string) $calculation['insurance_premium'],
                    'insuranceTermId'    => (int) $calculation['insurance_term_id'],
                    'funeralExpensesSum' => (string) $calculation['funeral_expenses_sum'],
                    'fot'                => (string) $calculation['fot'],
                ],
            ],
        ];

        try {
            $apiResponse = $this->submitOsgor($body);
        } catch (ProviderException $e) {
            return redirect()->route('osgor.getCalculator', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => $e->getMessage()]);
        }

        $insuranceId = $apiResponse['contract_id']
            ?? (($apiResponse['polis_sery'] ?? '') . ($apiResponse['polis_number'] ?? '') ?: null)
            ?? ($apiResponse['id'] ?? uniqid('osgor_'));

        $paymeUrl = $apiResponse['payme_url'] ?? null;
        $clickUrl = $apiResponse['click_url'] ?? null;

        Log::info('OSGOR order created', ['insurance_id' => $insuranceId]);

        return $this->createOrderAndRedirect([
            'product_name'             => __('insurance.osgor.product_name'),
            'amount'                   => $apiResponse['amount'] ?? $calculation['insurance_premium'],
            'insurance_id'             => (string) $insuranceId,
            'phone'                    => $applicant['phone'],
            'insurances_data'          => ['applicant' => $applicant, 'calculation' => $calculation],
            'insurances_response_data' => $apiResponse,
            'payme_url'                => $paymeUrl,
            'click_url'                => $clickUrl,
            'contractStartDate'        => $calculation['start_date'],
            'contractEndDate'          => $calculation['end_date'],
            'insuranceProductName'     => __('insurance.osgor.product_name'),
        ], self::SESSION_KEY);
    }
}
