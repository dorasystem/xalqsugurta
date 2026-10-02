<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Http\Controllers\Insurence\Concerns\PersonsFlow;
use App\Services\ProductSettings;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * "applicant → insured persons → term → confirm" products sold through the insurer's
 * website/accident API (calc + sale). Subclasses differ only by FLOW, session key and
 * productCode: accident (202), tourist (203). Routes: routes/insurence/{key}.php, same names.
 */
abstract class PersonsInsuranceController extends BaseInsuranceController
{
    use PersonsFlow;

    /** Product code of the insurer's website/accident API */
    abstract protected function productCode(): string;

    /** Provider calculator for one person, over the chosen (or earliest) start date and the configured term */
    protected function calculatePerson(int $sumInsured): array
    {
        return $this->calculatePersonsInsurance(
            $this->productCode(),
            $sumInsured,
            $this->sess('calculation.start_date') ?? $this->flow()['start_min'],
            $this->flow()['term_months'],
        );
    }

    // ─── Step 1: Applicant ────────────────────────────────────────────────────

    public function index(): View
    {
        return view('pages.insurence.flow.applicant', $this->flowViewData());
    }

    public function storeApplicant(Request $request): RedirectResponse
    {
        $applicant = $this->applicantFromRequest($request);
        if ($applicant instanceof RedirectResponse) {
            return $applicant;
        }

        $this->putSess('applicant', $applicant);

        return redirect()->route($this->getProductKey() . '.getPersons', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 2: Persons list ─────────────────────────────────────────────────

    public function getPersons(): View|RedirectResponse
    {
        $applicant = $this->sess('applicant');
        if (!$applicant) {
            return redirect()->route($this->getProductKey() . '.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.persons.persons', $this->flowViewData());
    }

    public function calculatePremium(Request $request): JsonResponse
    {
        $request->validate([
            'sum_insured' => ['required', 'integer', 'min:' . $this->flow()['min'], 'max:' . $this->flow()['max']],
        ]);

        try {
            $result = $this->calculatePerson((int) $request->input('sum_insured'));
        } catch (ProviderException $e) {
            return response()->json(['success' => false, 'message' => $this->providerErrorMessage($e)], 422);
        }

        $premium = (int) ($result['persons'][0]['insurancePremium'] ?? $result['cost']['insurancePremium'] ?? 0);

        return response()->json(['success' => true, 'premium' => $premium]);
    }

    public function addPerson(Request $request): RedirectResponse
    {
        if (!$this->sess('applicant')) {
            return redirect()->route($this->getProductKey() . '.index', ['locale' => getCurrentLocale()]);
        }

        $request->validate([
            'pinfl'           => ['required', 'string'],
            'passport_seria'  => ['required', 'string', 'max:4'],
            'passport_number' => ['required', 'digits:7'],
            'birth_date'      => ['required', 'date', 'before:today'],
            'firstname'       => ['required', 'string'],
            'lastname'        => ['required', 'string'],
            'sum_insured'     => ['required', 'integer', 'min:' . $this->flow()['min'], 'max:' . $this->flow()['max']],
        ]);

        $persons = $this->sess('persons', []);
        if (in_array($request->input('pinfl'), array_column($persons, 'pinfl'), true)) {
            return back()->withErrors(['person' => __t('messages.flow.person_exists')])->withInput();
        }

        $sumInsured = (int) $request->input('sum_insured');

        try {
            $calcResult = $this->calculatePerson($sumInsured);
            $premium    = (int) ($calcResult['persons'][0]['insurancePremium'] ?? $calcResult['cost']['insurancePremium'] ?? 0);
        } catch (ProviderException $e) {
            return back()->withErrors(['sum_insured' => $this->providerErrorMessage($e)])->withInput();
        }

        $persons[] = [
            'pinfl'               => $request->input('pinfl'),
            'passport_seria'      => strtoupper($request->input('passport_seria')),
            'passport_number'     => $request->input('passport_number'),
            'passport_issue_date' => $request->input('passport_issue_date', ''),
            'passport_issued_by'  => $request->input('passport_issued_by', ''),
            'birth_date'          => $request->input('birth_date'),
            'firstname'           => $request->input('firstname'),
            'lastname'            => $request->input('lastname'),
            'middlename'          => $request->input('middlename', ''),
            'address'             => $request->input('address', ''),
            'region_id'           => (int) $request->input('region_id', 10),
            'district_id'         => (int) $request->input('district_id', 0),
            'phone'               => $this->cleanPhone($request->input('phone') ?? ''),
            'resident_type'       => 1,
            'country_id'          => 210,
            'sum_insured'         => $sumInsured,
            'insurance_premium'   => $premium,
        ];

        $this->putSess('persons', $persons);

        return redirect()->route($this->getProductKey() . '.getPersons', ['locale' => getCurrentLocale()]);
    }

    /** Route: {locale}/{product}/persons/remove/{index} — the locale comes first */
    public function removePerson(string $locale, string $index): RedirectResponse
    {
        $persons = $this->sess('persons', []);
        array_splice($persons, (int) $index, 1);
        $this->putSess('persons', $persons);

        return redirect()->route($this->getProductKey() . '.getPersons', ['locale' => getCurrentLocale()]);
    }

    public function confirmPersons(Request $request): RedirectResponse
    {
        if (!$this->sess('applicant')) {
            return redirect()->route($this->getProductKey() . '.index', ['locale' => getCurrentLocale()]);
        }

        $persons = $this->sess('persons', []);
        if (empty($persons)) {
            return redirect()->route($this->getProductKey() . '.getPersons', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.at_least_one_person')]);
        }

        return redirect()->route($this->getProductKey() . '.getCalculator', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 3: Calculator (dates only) ─────────────────────────────────────

    public function getCalculator(): View|RedirectResponse
    {
        $applicant = $this->sess('applicant');
        $persons   = $this->sess('persons', []);

        if (!$applicant || empty($persons)) {
            return redirect()->route($this->getProductKey() . '.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.persons.term', $this->flowViewData());
    }

    public function storeCalculation(Request $request): RedirectResponse
    {
        $request->validate([
            'start_date' => ProductSettings::startDateRules($this->flow()),
        ]);

        $applicant = $this->sess('applicant');
        $persons   = $this->sess('persons', []);

        if (!$applicant || empty($persons)) {
            return redirect()->route($this->getProductKey() . '.index', ['locale' => getCurrentLocale()]);
        }

        $startDate    = $request->input('start_date');
        $endDate      = ProductSettings::endDate($this->flow(), $startDate);
        $totalSum     = array_sum(array_column($persons, 'sum_insured'));
        $totalPremium = array_sum(array_column($persons, 'insurance_premium'));

        $this->putSess('calculation', [
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'total_sum'     => $totalSum,
            'total_premium' => $totalPremium,
        ]);

        return redirect()->route($this->getProductKey() . '.getConfirm', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 4: Confirm + Submit ─────────────────────────────────────────────

    public function getConfirm(): View|RedirectResponse
    {
        $applicant   = $this->sess('applicant');
        $persons     = $this->sess('persons', []);
        $calculation = $this->sess('calculation');

        if (!$applicant || empty($persons) || !$calculation) {
            return redirect()->route($this->getProductKey() . '.index', ['locale' => getCurrentLocale()]);
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
        $persons     = $this->sess('persons', []);
        $calculation = $this->sess('calculation');

        if (!$applicant || empty($persons) || !$calculation) {
            return redirect()->route($this->getProductKey() . '.index', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.error_occurred')]);
        }

        // Totals always follow the current list (it may have changed after the term step)
        $calculation['total_sum']     = (int) array_sum(array_column($persons, 'sum_insured'));
        $calculation['total_premium'] = (int) array_sum(array_column($persons, 'insurance_premium'));

        $apiBody = $this->buildApiBody($applicant, $persons, $calculation);

        try {
            $apiResponse = $this->submitAccident($apiBody, config('provider.submit.' . $this->getProductKey()));
        } catch (ProviderException $e) {
            return redirect()->route($this->getProductKey() . '.getConfirm', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => $this->providerErrorMessage($e)]);
        }

        $contractId  = $apiResponse['contract_id'] ?? $apiResponse['id'] ?? $apiResponse['UUID'] ?? uniqid($this->getProductKey() . '_');
        $paymeUrl    = $apiResponse['payme_url']    ?? null;
        $clickUrl    = $apiResponse['click_url']    ?? null;

        Log::info(ucfirst($this->getProductKey()) . ' order created', ['contract_id' => $contractId]);

        return $this->createOrderAndRedirect([
            'product_name'             => __('insurance.' . $this->getProductKey() . '.product_name'),
            'amount'                   => $apiResponse['amount'] ?? $calculation['total_premium'],
            'insurance_id'             => (string) $contractId,
            'phone'                    => $applicant['phone'],
            'insurances_data'          => ['applicant' => $applicant, 'persons' => $persons, 'calculation' => $calculation],
            'insurances_response_data' => $apiResponse,
            'payme_url'                => $paymeUrl,
            'click_url'                => $clickUrl,
            'contractStartDate'        => $calculation['start_date'],
            'contractEndDate'          => $calculation['end_date'],
            'insuranceProductName'     => __('insurance.' . $this->getProductKey() . '.product_name'),
        ], $this->getProductKey());
    }

    // ─── Private: Build API body ──────────────────────────────────────────────

    /** Y-m-d as the sale API expects; accepts DD.MM.YYYY from lookups. Empty → null. */
    private function isoDate(?string $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            return Carbon::parse(str_replace('.', '-', $date))->format('Y-m-d');
        } catch (\Carbon\Exceptions\InvalidFormatException) {
            return null;
        }
    }

    private function buildApiBody(array $applicant, array $persons, array $calculation): array
    {
        $applicantPhone = $applicant['phone'] ?? '';

        $buildPerson = fn(array $p) => [
            'residentType' => 1,
            'passportData' => [
                'pinfl'     => $p['pinfl'],
                'seria'     => $p['passport_seria'],
                'number'    => $p['passport_number'],
                // pinfl-v2 returns no passport issue data; the sale API needs both fields, so use the
                // same placeholders Xalq Sug'urta accepted for OSAGO ("Not specified" + today)
                'issueDate' => $this->isoDate($p['passport_issue_date'] ?? null) ?? now()->format('Y-m-d'),
                'issuedBy'  => ($p['passport_issued_by'] ?? '') ?: 'Not specified',
            ],
            'fullName' => [
                'firstname'  => $p['firstname'],
                'lastname'   => $p['lastname'],
                'middlename' => $p['middlename'] ?? '',
            ],
            'birthDate'  => $this->isoDate($p['birth_date'] ?? null),
            'address'    => $p['address'],
            'countryId'  => 210,
            'regionId'   => (int) ($p['region_id'] ?? 0) ?: 10,
            'districtId' => (int) ($p['district_id'] ?? 0) ?: null,
            'phone'      => $p['phone'] ?: $applicantPhone,
        ];

        return [
            'applicant' => ['person' => $buildPerson($applicant)],
            'details'   => [
                'productCode' => $this->productCode(),
                'startDate'   => $calculation['start_date'],
                'endDate'     => $calculation['end_date'],
            ],
            'cost' => [
                'sumInsured'       => $calculation['total_sum'],
                'insurancePremium' => $calculation['total_premium'],
            ],
            'persons' => array_map(fn($p) => array_merge($buildPerson($p), [
                'sumInsured'       => $p['sum_insured'],
                'insurancePremium' => $p['insurance_premium'],
            ]), $persons),
        ];
    }
}
