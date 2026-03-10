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

final class AccidentController extends BaseInsuranceController
{
    private const SESSION_KEY = 'accident';

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
        return view('pages.insurence.accident.main', ['product' => $this->getProduct()]);
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
        ]));

        return redirect()->route('accident.getPersons', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 2: Persons list ─────────────────────────────────────────────────

    public function getPersons(): View|RedirectResponse
    {
        $applicant = $this->sess('applicant');
        if (!$applicant) {
            return redirect()->route('accident.index', ['locale' => getCurrentLocale()]);
        }

        $persons = $this->sess('persons', []);

        return view('pages.insurence.accident.persons', compact('applicant', 'persons'));
    }

    public function calculatePremium(Request $request): JsonResponse
    {
        $request->validate([
            'sum_insured' => ['required', 'integer', 'min:50000', 'max:1000000'],
        ]);

        try {
            $result = $this->calculateAccident((int) $request->input('sum_insured'));
        } catch (ProviderException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $premium = (int) ($result['persons'][0]['insurancePremium'] ?? $result['cost']['insurancePremium'] ?? 0);

        return response()->json(['success' => true, 'premium' => $premium]);
    }

    public function addPerson(Request $request): RedirectResponse
    {
        if (!$this->sess('applicant')) {
            return redirect()->route('accident.index', ['locale' => getCurrentLocale()]);
        }

        $request->validate([
            'pinfl'           => ['required', 'string'],
            'passport_seria'  => ['required', 'string', 'max:4'],
            'passport_number' => ['required', 'digits:7'],
            'birth_date'      => ['required', 'date', 'before:today'],
            'firstname'       => ['required', 'string'],
            'lastname'        => ['required', 'string'],
            'sum_insured'     => ['required', 'integer', 'min:50000', 'max:1000000'],
        ]);

        $sumInsured = (int) $request->input('sum_insured');

        try {
            $calcResult = $this->calculateAccident($sumInsured);
            $premium    = (int) ($calcResult['persons'][0]['insurancePremium'] ?? $calcResult['cost']['insurancePremium'] ?? 0);
        } catch (ProviderException $e) {
            return back()->withErrors(['sum_insured' => $e->getMessage()])->withInput();
        }

        $persons   = $this->sess('persons', []);
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

        return redirect()->route('accident.getPersons', ['locale' => getCurrentLocale()]);
    }

    public function removePerson(string $index): RedirectResponse
    {
        $persons = $this->sess('persons', []);
        array_splice($persons, (int) $index, 1);
        $this->putSess('persons', $persons);

        return redirect()->route('accident.getPersons', ['locale' => getCurrentLocale()]);
    }

    public function confirmPersons(Request $request): RedirectResponse
    {
        if (!$this->sess('applicant')) {
            return redirect()->route('accident.index', ['locale' => getCurrentLocale()]);
        }

        $persons = $this->sess('persons', []);
        if (empty($persons)) {
            return redirect()->route('accident.getPersons', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.at_least_one_person')]);
        }

        return redirect()->route('accident.getCalculator', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 3: Calculator (dates only) ─────────────────────────────────────

    public function getCalculator(): View|RedirectResponse
    {
        $applicant = $this->sess('applicant');
        $persons   = $this->sess('persons', []);

        if (!$applicant || empty($persons)) {
            return redirect()->route('accident.index', ['locale' => getCurrentLocale()]);
        }

        $totalSum     = array_sum(array_column($persons, 'sum_insured'));
        $totalPremium = array_sum(array_column($persons, 'insurance_premium'));
        $calculation  = $this->sess('calculation', []);

        return view('pages.insurence.accident.calculator', compact('applicant', 'persons', 'totalSum', 'totalPremium', 'calculation'));
    }

    public function storeCalculation(Request $request): RedirectResponse
    {
        $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $applicant = $this->sess('applicant');
        $persons   = $this->sess('persons', []);

        if (!$applicant || empty($persons)) {
            return redirect()->route('accident.index', ['locale' => getCurrentLocale()]);
        }

        $startDate    = $request->input('start_date');
        $endDate      = Carbon::parse($startDate)->addYear()->subDay()->format('Y-m-d');
        $totalSum     = array_sum(array_column($persons, 'sum_insured'));
        $totalPremium = array_sum(array_column($persons, 'insurance_premium'));

        $this->putSess('calculation', [
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'total_sum'     => $totalSum,
            'total_premium' => $totalPremium,
        ]);

        return redirect()->route('accident.getConfirm', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 4: Confirm + Submit ─────────────────────────────────────────────

    public function getConfirm(): View|RedirectResponse
    {
        $applicant   = $this->sess('applicant');
        $persons     = $this->sess('persons', []);
        $calculation = $this->sess('calculation');

        if (!$applicant || empty($persons) || !$calculation) {
            return redirect()->route('accident.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.accident.confirm', compact('applicant', 'persons', 'calculation'));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $applicant   = $this->sess('applicant');
        $persons     = $this->sess('persons', []);
        $calculation = $this->sess('calculation');

        if (!$applicant || empty($persons) || !$calculation) {
            return redirect()->route('accident.index', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.error_occurred')]);
        }

        $apiBody = $this->buildAccidentApiBody($applicant, $persons, $calculation);

        try {
            $apiResponse = $this->submitAccident($apiBody);
        } catch (ProviderException $e) {
            return redirect()->route('accident.getConfirm', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => $e->getMessage()]);
        }

        $contractId  = $apiResponse['contract_id'] ?? $apiResponse['id'] ?? $apiResponse['UUID'] ?? uniqid('acc_');
        $paymeUrl    = $apiResponse['payme_url']    ?? null;
        $clickUrl    = $apiResponse['click_url']    ?? null;

        Log::info('Accident order created', ['contract_id' => $contractId]);

        return $this->createOrderAndRedirect([
            'product_name'             => __('insurance.accident.product_name'),
            'amount'                   => $apiResponse['amount'] ?? $calculation['total_premium'],
            'insurance_id'             => (string) $contractId,
            'phone'                    => $applicant['phone'],
            'insurances_data'          => ['applicant' => $applicant, 'persons' => $persons, 'calculation' => $calculation],
            'insurances_response_data' => $apiResponse,
            'payme_url'                => $paymeUrl,
            'click_url'                => $clickUrl,
            'contractStartDate'        => $calculation['start_date'],
            'contractEndDate'          => $calculation['end_date'],
            'insuranceProductName'     => __('insurance.accident.product_name'),
        ], self::SESSION_KEY);
    }

    // ─── Private: Build API body ──────────────────────────────────────────────

    private function buildAccidentApiBody(array $applicant, array $persons, array $calculation): array
    {
        $applicantPhone = $applicant['phone'] ?? '';

        $buildPerson = fn(array $p) => [
            'residentType' => 1,
            'passportData' => [
                'pinfl'     => $p['pinfl'],
                'seria'     => $p['passport_seria'],
                'number'    => $p['passport_number'],
                'issueDate' => $p['passport_issue_date'] ?? '',
                'issuedBy'  => $p['passport_issued_by']  ?? '',
            ],
            'fullName' => [
                'firstname'  => $p['firstname'],
                'lastname'   => $p['lastname'],
                'middlename' => $p['middlename'] ?? '',
            ],
            'birthDate'  => $p['birth_date'],
            'address'    => $p['address'],
            'countryId'  => 210,
            'regionId'   => (int) ($p['region_id']   ?? 10),
            'districtId' => (int) ($p['district_id'] ?? 0),
            'phone'      => $p['phone'] ?: $applicantPhone,
        ];

        return [
            'applicant' => ['person' => $buildPerson($applicant)],
            'details'   => [
                'productCode' => '202',
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
