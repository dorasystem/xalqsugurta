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

/**
 * OSGOR (employer liability): organization by INN → salary fund + start date → confirm → payment.
 * The premium comes from the insurer's calculator (eshop/osgorcalc) and is always recalculated
 * on the server; the browser only sends the salary fund and the start date.
 */
final class OsgorController extends BaseInsuranceController
{
    private const SESSION_KEY = 'osgor';

    private const FLOW = [
        'key'  => self::SESSION_KEY,
        'icon' => 'bi-person-badge',
    ];

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
        return view('pages.insurence.osgor.organization', $this->flowViewData());
    }

    public function storeApplicant(Request $request): RedirectResponse
    {
        $request->merge(['phone' => $this->cleanPhone($request->input('phone'))]);

        $request->validate([
            'inn'   => ['required', 'digits:9'],
            'phone' => ['required', 'regex:/^998[0-9]{9}$/'],
        ], [
            'inn.required' => __('messages.inn_required'),
            'inn.digits'   => __('messages.inn_invalid'),
        ]);

        try {
            $org = $this->findOrganizationByInn($request->input('inn'));
        } catch (ProviderException) {
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
            'phone'              => $request->input('phone'),
            'regionId'           => $org['regionId']            ?? (isset($org['districtSoatoCode']) ? substr($org['districtSoatoCode'], 0, 2) : '10'),
            'ownershipFormId'    => $org['ownershipFormId']     ?? '130',
        ]);

        return redirect()->route('osgor.getCalculator', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 2: Calculator ───────────────────────────────────────────────────

    public function getCalculator(): View|RedirectResponse
    {
        if (!$this->sess('applicant')) {
            return redirect()->route('osgor.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.osgor.calculator', $this->flowViewData());
    }

    /** AJAX: live premium while the salary fund is typed */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate($this->calculationRules());

        $applicant = $this->sess('applicant');
        if (!$applicant) {
            return response()->json(['success' => false, 'message' => __('messages.error_occurred')], 422);
        }

        try {
            $calculation = $this->calculation($applicant, (float) $request->input('fot'), $request->input('start_date'));
        } catch (ProviderException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $calculation]);
    }

    public function storeCalculation(Request $request): RedirectResponse
    {
        $applicant = $this->sess('applicant');
        if (!$applicant) {
            return redirect()->route('osgor.index', ['locale' => getCurrentLocale()]);
        }

        $request->validate($this->calculationRules());

        try {
            $calculation = $this->calculation($applicant, (float) $request->input('fot'), $request->input('start_date'));
        } catch (ProviderException $e) {
            return back()->withErrors(['fot' => $e->getMessage()])->withInput();
        }

        $this->putSess('calculation', $calculation);

        return redirect()->route('osgor.getConfirm', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 3: Confirm + Submit ─────────────────────────────────────────────

    public function getConfirm(): View|RedirectResponse
    {
        if (!$this->sess('applicant') || !$this->sess('calculation')) {
            return redirect()->route('osgor.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.flow.confirm', $this->flowViewData([
            'product' => $this->getProduct(),
        ]));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $request->validate([
            'offerta_agreed' => $this->offertaRule(),
        ], [
            'offerta_agreed.required' => __('messages.offerta_required'),
            'offerta_agreed.accepted' => __('messages.offerta_required'),
        ]);

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
            return redirect()->route('osgor.getConfirm', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => $e->getMessage()]);
        }

        $insuranceId = $apiResponse['contract_id']
            ?? (($apiResponse['polis_sery'] ?? '') . ($apiResponse['polis_number'] ?? '') ?: null)
            ?? ($apiResponse['id'] ?? uniqid('osgor_'));

        Log::info('OSGOR order created', ['insurance_id' => $insuranceId]);

        return $this->createOrderAndRedirect([
            'product_name'             => __('insurance.osgor.product_name'),
            'amount'                   => $apiResponse['amount'] ?? $calculation['insurance_premium'],
            'insurance_id'             => (string) $insuranceId,
            'phone'                    => $applicant['phone'],
            'insurances_data'          => ['applicant' => $applicant, 'calculation' => $calculation],
            'insurances_response_data' => $apiResponse,
            'payme_url'                => $apiResponse['payme_url'] ?? null,
            'click_url'                => $apiResponse['click_url'] ?? null,
            'contractStartDate'        => $calculation['start_date'],
            'contractEndDate'          => $calculation['end_date'],
            'insuranceProductName'     => __('insurance.osgor.product_name'),
        ], self::SESSION_KEY);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function calculationRules(): array
    {
        return [
            'fot'        => ['required', 'numeric', 'min:1'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /** Premium and terms from the insurer's calculator, for the policy period starting on $startDate (one year) */
    private function calculation(array $applicant, float $fot, string $startDate): array
    {
        $result = $this->calculateOsgor($applicant['oked'], $fot);

        return [
            'fot'                  => $fot,
            'start_date'           => Carbon::parse($startDate)->format('Y-m-d'),
            'end_date'             => Carbon::parse($startDate)->addYear()->subDay()->format('Y-m-d'),
            'insurance_premium'    => (float) ($result['insurancePremium']   ?? $result['premium']    ?? 0),
            'insurance_sum'        => (float) ($result['insuranceSum']       ?? $result['sumInsured'] ?? 0),
            'insurance_rate'       => (float) ($result['insuranceRate']      ?? $result['rate']       ?? 0),
            'funeral_expenses_sum' => (float) ($result['funeralExpensesSum'] ?? 0),
            'insurance_term_id'    => (int)   ($result['insuranceTermId']    ?? 4),
        ];
    }

    /** View data in the unified flow shape (pages/insurence/flow/confirm + osgor views) */
    private function flowViewData(array $extra = []): array
    {
        $locale      = getCurrentLocale();
        $applicant   = $this->sess('applicant');
        $calculation = $this->sess('calculation', []);

        $period = !empty($calculation['start_date'])
            ? Carbon::parse($calculation['start_date'])->format('d.m.Y') . ' – ' . Carbon::parse($calculation['end_date'])->format('d.m.Y')
            : null;
        $premium = !empty($calculation['insurance_premium']) ? (int) round($calculation['insurance_premium']) : null;
        $orgUrl  = route('osgor.index', ['locale' => $locale]);
        $calcUrl = route('osgor.getCalculator', ['locale' => $locale]);

        return array_merge([
            'flow'            => self::FLOW,
            'applicant'       => $applicant,
            'calculation'     => $calculation,
            'premiumTotal'    => $premium,
            'flowSteps'       => [
                __t('messages.flow.organization'),
                __t('messages.flow.fot_title'),
                __t('messages.confirm_details'),
                __t('messages.flow.payment'),
            ],
            'flowUrls'        => [$orgUrl, $calcUrl, route('osgor.getConfirm', ['locale' => $locale])],
            'summaryItems'    => [
                'applicant' => [__t('messages.flow.organization'), $applicant['name'] ?? null],
                'sum'       => [__t('messages.fond_oplaty_truda'), !empty($calculation['fot']) ? formatMoney($calculation['fot']) : null],
                'period'    => [__t('messages.flow.period'), $period],
            ],
            'applicantTitle'  => __t('messages.flow.organization'),
            'applicantReview' => $applicant ? [
                __t('messages.organization_name')   => $applicant['name'],
                __t('messages.inn')                 => $applicant['inn'],
                __t('messages.representative_name') => $applicant['representativeName'] ?: null,
                __t('messages.oked')                => $applicant['oked'] ?: null,
                __('messages.phone_number')         => formatPhone($applicant['phone']),
            ] : [],
            'confirmBlocks'   => [
                ['title' => __t('messages.flow.policy_terms'), 'editUrl' => $calcUrl, 'items' => [
                    __t('messages.fond_oplaty_truda')   => !empty($calculation['fot']) ? formatMoney($calculation['fot']) : null,
                    __t('messages.insurance_rate')      => isset($calculation['insurance_rate']) ? rtrim(rtrim((string) $calculation['insurance_rate'], '0'), '.') . '%' : null,
                    __t('messages.flow.period')         => $period,
                    __('messages.insurance_premium')    => $premium ? formatMoney($premium) : null,
                ]],
            ],
        ], $extra);
    }
}
