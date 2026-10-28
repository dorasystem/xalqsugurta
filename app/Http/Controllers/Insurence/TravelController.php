<?php

namespace App\Http\Controllers\Insurence;

use App\Exceptions\ProviderException;
use App\Exceptions\TravelApiException;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\Travel\TravelApi;
use App\Services\Travel\TravelPolicyService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Travel insurance abroad (ВЗР): trip + program → applicant → travelers → confirm → payment.
 * Priced by the insurer's travel/calc and sold by travel/sale (docs/API.md → Travel). The price is
 * always recalculated on the server from the trip in the session; the browser never sends one.
 * Payment goes only through the insurer's own Payme / Click links: the policy is issued when the
 * insurer sees the payment, so the site's cashbox is not offered (Order::INSURER_PAYMENT_ONLY).
 */
final class TravelController extends BaseInsuranceController
{
    private const SESSION_KEY = 'travel';

    public const FLOW = [
        'key'  => self::SESSION_KEY,
        'icon' => 'bi-airplane',
    ];

    /** reference/trip_purpose ids (names are ours: the API has them only in Russian) */
    public const PURPOSES = [0, 1, 4, 5, 6, 7];

    /** reference/travelers_type: id => smallest number of travelers */
    public const TRAVELER_TYPES = [0 => 1, 1 => 3, 2 => 10];

    /** reference/multi_days: id => [stay days, policy days] */
    public const MULTI_DAYS = [1 => [30, 92], 2 => [90, 183], 3 => [60, 365], 4 => [90, 365], 5 => [180, 365]];

    private const MAX_TRAVELERS = 100;

    public function __construct(
        private readonly TravelApi $api,
        OrderService $orderService,
    ) {
        parent::__construct($orderService);
    }

    protected function getProductKey(): string
    {
        return self::SESSION_KEY;
    }

    // ─── Step 1: Trip + program ───────────────────────────────────────────────

    public function index(): View
    {
        try {
            $countries = $this->api->countries();
        } catch (ProviderException) {
            $countries = [];
        }

        return view('pages.insurence.travel.trip', $this->flowViewData([
            'countries'      => $this->countryOptions($countries),
            'referenceError' => $countries === [] ? __t('messages.flow.insurer_unavailable') : null,
        ]));
    }

    /** AJAX: the price of every program available for the trip */
    public function calculate(Request $request): JsonResponse
    {
        try {
            $countries = $this->api->countries();
        } catch (ProviderException) {
            return response()->json(['success' => false, 'message' => __t('messages.flow.insurer_unavailable')], 422);
        }

        $trip = $this->validatedTrip($request, $countries, withProgram: false);

        try {
            $result = $this->api->calc($this->calcBody($trip, $trip['birthdates']));
        } catch (TravelApiException $e) {
            return response()->json(['success' => false, 'message' => $this->errorMessage($e)], 422);
        }

        return response()->json([
            'success'  => true,
            'trip'     => $this->tripFacts($result),
            'programs' => array_map(fn (array $p): array => [
                'id'        => (int) $p['id'],
                'name'      => (string) ($p['name'] ?? ''),
                'liability' => (int) ($p['liability'] ?? 0),
                'premium'   => (float) ($p['insurance_premium'] ?? 0),
                'price'     => formatMoney((int) round((float) ($p['insurance_premium'] ?? 0))),
                'available' => (int) ($p['available'] ?? 0) === 1,
                'reason'    => (string) ($p['reason'] ?? ''),
            ], array_values((array) ($result['programs'] ?? []))),
        ]);
    }

    public function storeTrip(Request $request): RedirectResponse
    {
        try {
            $countries = $this->api->countries();
        } catch (ProviderException) {
            return back()->withErrors(['program' => __t('messages.flow.insurer_unavailable')])->withInput();
        }

        $trip = $this->validatedTrip($request, $countries, withProgram: true);

        try {
            $calc = $this->api->calc($this->calcBody($trip, $trip['birthdates']));
        } catch (TravelApiException $e) {
            return back()->withErrors(['program' => $this->errorMessage($e)])->withInput();
        }

        if ((int) ($calc['available'] ?? 0) !== 1) {
            return back()->withErrors(['program' => $calc['reason'] ?? __t('messages.travel.errors.57')])->withInput();
        }

        // `quote` is priced by the birth dates typed here; `calc` (by the travelers' real ones) comes
        // after the travelers step. A changed trip may need another number of travelers: keep those found.
        $this->putSess('trip', $trip);
        $this->putSess('quote', $this->calcSummary($calc));
        $this->forgetPrice();

        return redirect()->route('travel.getApplicant', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 2: Applicant (the policy holder) ────────────────────────────────

    public function getApplicant(): View|RedirectResponse
    {
        if (!$this->sess('trip')) {
            return redirect()->route('travel.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.travel.applicant', $this->flowViewData([
            'regions' => $this->regionOptions(),
        ]));
    }

    public function storeApplicant(Request $request): RedirectResponse
    {
        if (!$this->sess('trip')) {
            return redirect()->route('travel.index', ['locale' => getCurrentLocale()]);
        }

        $request->validate([
            'region_id' => ['required', 'integer', Rule::in(array_keys($this->regionOptions()))],
            'address'   => ['nullable', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:100'],
        ]);

        $applicant = $this->applicantFromRequest($request);
        if ($applicant instanceof RedirectResponse) {
            return $applicant;
        }

        if (!$this->isAdult($applicant['birth_date'])) {
            return back()->withErrors(['pinfl' => __t('messages.travel.errors.58')])->withInput();
        }

        $address = trim((string) $request->input('address')) ?: $applicant['address'];
        if ($address === '') {
            return back()->withErrors(['address' => __t('messages.travel.address_required')])->withInput();
        }

        $applicant = array_merge($applicant, [
            'region_id' => (int) $request->input('region_id'),
            'address'   => $address,
            'email'     => (string) $request->input('email', ''),
        ]);

        $this->putSess('applicant', $applicant);

        // The applicant travels too: first in the list (parents come first in a family)
        $travelers = array_values(array_filter($this->sess('travelers', []), fn (array $t): bool => $t['pinfl'] !== $applicant['pinfl']));
        if ($request->boolean('travels')) {
            array_unshift($travelers, $this->travelerFrom($applicant));
        }
        $this->putSess('travelers', $travelers);
        $this->forgetPrice();

        return redirect()->route('travel.getTravelers', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 3: Travelers ────────────────────────────────────────────────────

    public function getTravelers(): View|RedirectResponse
    {
        if (!$this->sess('trip') || !$this->sess('applicant')) {
            return redirect()->route('travel.index', ['locale' => getCurrentLocale()]);
        }

        return view('pages.insurence.travel.travelers', $this->flowViewData());
    }

    public function addTraveler(Request $request): RedirectResponse
    {
        if (!$this->sess('trip') || !$this->sess('applicant')) {
            return redirect()->route('travel.index', ['locale' => getCurrentLocale()]);
        }

        $request->validate($this->personLookupRules());

        $travelers = $this->sess('travelers', []);
        if (count($travelers) >= $this->expectedTravelers()) {
            return back()->withErrors(['pinfl' => __t('messages.travel.travelers_full')])->withInput();
        }

        try {
            $person = $this->lookupPerson($request);
        } catch (ProviderException $e) {
            return back()->withErrors(['pinfl' => $this->lookupErrorMessage($e)])->withInput();
        }

        if (empty($person['currentPinfl'] ?? null)) {
            return back()->withErrors(['pinfl' => __('messages.person_not_found')])->withInput();
        }

        $traveler = $this->travelerFrom(array_merge($this->normalizePerson($person, $request), [
            'pinfl'      => (string) $person['currentPinfl'],
            'birth_date' => $this->personBirthDate($person, $request),
            'gender'     => $this->personGender($person),
        ]));

        if (in_array($traveler['pinfl'], array_column($travelers, 'pinfl'), true)) {
            return back()->withErrors(['pinfl' => __t('messages.flow.person_exists')])->withInput();
        }

        $travelers[] = $traveler;
        $this->putSess('travelers', $travelers);
        $this->forgetPrice();

        return redirect()->route('travel.getTravelers', ['locale' => getCurrentLocale()]);
    }

    /** Route: {locale}/travel/travelers/remove/{index} — the locale comes first */
    public function removeTraveler(string $locale, string $index): RedirectResponse
    {
        $travelers = $this->sess('travelers', []);
        array_splice($travelers, (int) $index, 1);
        $this->putSess('travelers', $travelers);
        $this->forgetPrice();

        return redirect()->route('travel.getTravelers', ['locale' => getCurrentLocale()]);
    }

    /** All travelers found: price them by their real birth dates, then the confirm page */
    public function confirmTravelers(): RedirectResponse
    {
        $trip      = $this->sess('trip');
        $travelers = $this->sess('travelers', []);

        if (!$trip || !$this->sess('applicant')) {
            return redirect()->route('travel.index', ['locale' => getCurrentLocale()]);
        }

        if (count($travelers) !== $this->expectedTravelers()) {
            return redirect()->route('travel.getTravelers', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __t('messages.travel.travelers_count', ['count' => $this->expectedTravelers()])]);
        }

        $error = $this->recalculate($trip, $travelers);
        if ($error !== null) {
            return redirect()->route('travel.getTravelers', ['locale' => getCurrentLocale()])->withErrors(['error' => $error]);
        }

        return redirect()->route('travel.getConfirm', ['locale' => getCurrentLocale()]);
    }

    // ─── Step 4: Confirm + sale ───────────────────────────────────────────────

    public function getConfirm(): View|RedirectResponse
    {
        if (!$this->readyForSale()) {
            return redirect()->route('travel.index', ['locale' => getCurrentLocale()]);
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

        if (!$this->readyForSale()) {
            return redirect()->route('travel.index', ['locale' => getCurrentLocale()])
                ->withErrors(['error' => __('messages.error_occurred')]);
        }

        $applicant = $this->sess('applicant');
        $travelers = $this->sess('travelers');
        $trip      = $this->sess('trip');
        $calc      = $this->sess('calc');
        $orderKey  = $this->orderKey();
        $body      = $this->saleBody($orderKey, $trip, $applicant, $travelers, $calc);
        $confirm   = fn (string $message) => redirect()->route('travel.getConfirm', ['locale' => getCurrentLocale()])->withErrors(['error' => $message]);

        try {
            try {
                $sale = $this->api->sale($body);
            } catch (TravelApiException $e) {
                // The return address is agreed with the insurer; without it the sale still works
                if ($e->result !== 49 || !isset($body['policy']['return_url'])) {
                    throw $e;
                }
                unset($body['policy']['return_url']);
                $sale = $this->api->sale($body);
            }
        } catch (TravelApiException $e) {
            Log::warning('Travel sale refused', ['order_key' => $orderKey, 'result' => $e->result, 'status' => $e->getCode()]);

            return match ($e->result) {
                // The price changed (exchange rate, tariff): show the new one
                40      => $confirm(trim(__t('messages.travel.errors.40') . ' ' . $this->recalculate($trip, $travelers))),
                // Refused for good: a new attempt needs a new order key
                51, 57  => tap($confirm($this->errorMessage($e)), fn () => $this->forgetOrderKey()),
                // Data to fix (50) or anything else: the same key can be sent again
                default => $confirm($this->errorMessage($e)),
            };
        }

        $contractId = (int) ($sale['contract_id'] ?? 0);

        Log::info('Travel order created', ['contract_id' => $contractId, 'order_key' => $orderKey]);

        return $this->createOrderAndRedirect([
            'product_name'             => __('insurance.travel.product_name'),
            'amount'                   => (float) ($sale['amount'] ?? $calc['insurance_premium']),
            'insurance_id'             => (string) ($contractId ?: $orderKey),
            'phone'                    => $applicant['phone'],
            'insurances_data'          => [
                'order_key'   => $orderKey,
                'applicant'   => $applicant,
                'travelers'   => $travelers,
                'trip'        => $trip,
                'calculation' => $calc,
            ],
            'insurances_response_data' => $sale,
            'payme_url'                => $sale['payme_url'] ?? null,
            'click_url'                => $sale['click_url'] ?? null,
            'contractStartDate'        => Carbon::createFromFormat('d.m.Y', $calc['start_date'])->format('Y-m-d'),
            'contractEndDate'          => Carbon::createFromFormat('d.m.Y', $calc['policy_end_date'])->format('Y-m-d'),
            'insuranceProductName'     => __('insurance.travel.product_name'),
        ], self::SESSION_KEY);
    }

    // ─── Return from Payme / Click ────────────────────────────────────────────

    /** policy.return_url: the insurer sends the client back here after paying; the policy may already be out */
    public function paid(Request $request, TravelPolicyService $policies): RedirectResponse
    {
        $key   = (string) $request->query('order', '');
        $order = preg_match('/^[A-Za-z0-9._:-]{1,64}$/', $key)
            ? Order::where('product_key', self::SESSION_KEY)->where('insurances_data->order_key', $key)->latest('id')->first()
            : null;

        if (!$order) {
            return redirect()->route('home', ['locale' => getCurrentLocale()]);
        }

        $policies->refresh($order);

        return redirect()->route('payment.show', ['locale' => getCurrentLocale(), 'orderId' => $order->id]);
    }

    // ─── Private: trip ────────────────────────────────────────────────────────

    /** Trip from the step-1 form, dates as Y-m-d */
    private function validatedTrip(Request $request, array $countries, bool $withProgram): array
    {
        $data = $request->validate([
            'policy_type'    => ['required', 'integer', Rule::in([0, 1])],
            'multi_days'     => ['nullable', 'required_if:policy_type,1', 'integer', Rule::in(array_keys(self::MULTI_DAYS))],
            'start_date'     => ['required', 'date', 'after_or_equal:today'],
            'end_date'       => ['nullable', 'required_if:policy_type,0', 'date', 'after_or_equal:start_date'],
            'countries'      => ['required', 'array', 'min:1', 'max:20'],
            'countries.*'    => ['integer', Rule::in(array_keys($countries))],
            'trip_purpose'   => ['required', 'integer', Rule::in(self::PURPOSES)],
            'travelers_type' => ['required', 'integer', Rule::in(array_keys(self::TRAVELER_TYPES))],
            'birthdates'     => ['required', 'array', 'min:1', 'max:' . self::MAX_TRAVELERS],
            'birthdates.*'   => ['required', 'date', 'before_or_equal:today'],
            'program'        => $withProgram ? ['required', 'integer', 'min:1'] : ['nullable'],
        ], [
            'countries.required'  => __t('messages.travel.countries_required'),
            'birthdates.required' => __t('messages.travel.birthdates_required'),
            'program.required'    => __t('messages.travel.program_required'),
        ]);

        $multi = (int) $data['policy_type'] === 1;

        return [
            'policy_type'    => (int) $data['policy_type'],
            'multi_days'     => $multi ? (int) $data['multi_days'] : null,
            'start_date'     => Carbon::parse($data['start_date'])->format('Y-m-d'),
            'end_date'       => $multi ? null : Carbon::parse($data['end_date'])->format('Y-m-d'),
            'countries'      => array_values(array_unique(array_map('intval', $data['countries']))),
            'trip_purpose'   => (int) $data['trip_purpose'],
            'travelers_type' => (int) $data['travelers_type'],
            'birthdates'     => array_map(fn ($d) => Carbon::parse($d)->format('Y-m-d'), array_values($data['birthdates'])),
            'program'        => $withProgram ? (int) $data['program'] : null,
        ];
    }

    /** travel/calc body; $birthdates Y-m-d in traveler order */
    private function calcBody(array $trip, array $birthdates): array
    {
        $policy = [
            'policy_type'    => $trip['policy_type'],
            'start_date'     => self::apiDate($trip['start_date']),
            'country'        => implode(':', $trip['countries']),
            'trip_purpose'   => $trip['trip_purpose'],
            'travelers_type' => $trip['travelers_type'],
        ];

        if ($trip['policy_type'] === 1) {
            $policy['multi_days'] = $trip['multi_days'];
        } else {
            $policy['end_date'] = self::apiDate($trip['end_date']);
        }

        if (!empty($trip['program'])) {
            $policy['program'] = $trip['program'];
        }

        return [
            'policy'    => $policy,
            'travelers' => array_map(fn (string $d): array => ['birthdate' => self::apiDate($d)], $birthdates),
        ];
    }

    /** Re-prices the trip with the travelers' real birth dates; returns an error message or null */
    private function recalculate(array $trip, array $travelers): ?string
    {
        try {
            $calc = $this->api->calc($this->calcBody($trip, array_column($travelers, 'birth_date')));
        } catch (TravelApiException $e) {
            return $this->errorMessage($e);
        }

        if ((int) ($calc['available'] ?? 0) !== 1) {
            return $calc['reason'] ?? __t('messages.travel.errors.57');
        }

        $this->putSess('calc', $this->calcSummary($calc));

        return null;
    }

    /** What the confirm page, the sale and the order need from a travel/calc answer */
    private function calcSummary(array $calc): array
    {
        return [
            'program'           => (int) ($calc['program'] ?? 0),
            'program_name'      => (string) ($calc['program_name'] ?? ''),
            'program_liability' => (int) ($calc['program_liability'] ?? 0),
            'start_date'        => (string) $calc['start_date'],
            'end_date'          => (string) ($calc['end_date'] ?? ''),
            'policy_end_date'   => (string) ($calc['policy_end_date'] ?? $calc['end_date'] ?? ''),
            'days'              => (int) ($calc['days'] ?? 0),
            'insured_days'      => (int) ($calc['insured_days'] ?? 0),
            'is_schengen'       => (int) ($calc['is_schengen'] ?? 0) === 1,
            'exchange_rate'     => (float) ($calc['exchange_rate'] ?? 0),
            'premium_usd'       => (float) ($calc['premium_usd'] ?? 0),
            'insurance_premium' => (float) ($calc['insurance_premium'] ?? 0),
            'travelers'         => array_map(fn (array $t): array => [
                'age'         => (int) ($t['age'] ?? 0),
                'premium'     => (float) ($t['premium'] ?? 0),
                'premium_uzs' => (float) ($t['premium_uzs'] ?? 0),
            ], array_values((array) ($calc['travelers'] ?? []))),
        ];
    }

    /** Dates and days from a calc answer, for the trip summary under the program cards */
    private function tripFacts(array $calc): array
    {
        return [
            'start_date'      => $calc['start_date'] ?? null,
            'policy_end_date' => $calc['policy_end_date'] ?? null,
            'days'            => (int) ($calc['days'] ?? 0),
            'insured_days'    => (int) ($calc['insured_days'] ?? 0),
            'schengen'        => (int) ($calc['is_schengen'] ?? 0) === 1,
        ];
    }

    // ─── Private: people and sale ─────────────────────────────────────────────

    /** The fields travel/sale needs for a traveler, from a normalized person */
    private function travelerFrom(array $p): array
    {
        return [
            'pinfl'               => $p['pinfl'],
            'passport_seria'      => $p['passport_seria'],
            'passport_number'     => $p['passport_number'],
            'passport_issue_date' => $p['passport_issue_date'] ?? '',
            'passport_issued_by'  => $p['passport_issued_by'] ?? '',
            'lastname'            => $p['lastname'],
            'firstname'           => $p['firstname'],
            'middlename'          => $p['middlename'] ?? '',
            'birth_date'          => $p['birth_date'],
            'gender'              => in_array($p['gender'] ?? '', ['2', 'f'], true) ? '2' : '1',
        ];
    }

    private function saleBody(string $orderKey, array $trip, array $applicant, array $travelers, array $calc): array
    {
        $policy = $this->calcBody($trip, [])['policy'];

        $returnUrl = route('travel.paid', ['locale' => getCurrentLocale(), 'order' => $orderKey]);
        if (config('provider.travel.return_url') && str_starts_with($returnUrl, 'https://') && strlen($returnUrl) <= 300) {
            $policy['return_url'] = $returnUrl;
        }

        $person = fn (array $p): array => array_filter([
            'resident'           => 1,
            'pinfl'              => $p['pinfl'],
            'seria'              => $p['passport_seria'],
            'number'             => $p['passport_number'],
            'lastname'           => $p['lastname'],
            'firstname'          => $p['firstname'],
            'middlename'         => $p['middlename'] ?? '',
            'birthdate'          => self::apiDate($p['birth_date']),
            'gender'             => ($p['gender'] ?? '1') === '2' ? 0 : 1,
            'passport_issued_by' => $p['passport_issued_by'] ?? '',
            'passport_date'      => self::apiDate($p['passport_issue_date'] ?? null),
        ], fn ($v) => $v !== '' && $v !== null);

        return [
            'order_id'  => $orderKey,
            'policy'    => $policy,
            'applicant' => $person($applicant) + array_filter([
                'region'  => (int) $applicant['region_id'],
                'address' => $applicant['address'],
                'phone'   => $applicant['phone'],
                'email'   => $applicant['email'] ?? '',
            ]),
            'travelers' => array_map($person, $travelers),
            'cost'      => ['insurance_premium' => $calc['insurance_premium']],
        ];
    }

    /** order_id of travel/sale: one per attempt, so a retry never makes a second contract */
    private function orderKey(): string
    {
        $key = $this->sess('order_key');

        if (!$key) {
            $key = 'XS-' . now()->format('ymd') . '-' . Str::upper(Str::random(10));
            $this->putSess('order_key', $key);
        }

        return $key;
    }

    /** The order changed (or was refused for good): the next sale is a new order */
    private function forgetOrderKey(): void
    {
        session()->forget(self::SESSION_KEY . '.order_key');
    }

    /** Trip or travelers changed: the final price and the order key no longer apply */
    private function forgetPrice(): void
    {
        session()->forget(self::SESSION_KEY . '.calc');
        $this->forgetOrderKey();
    }

    private function readyForSale(): bool
    {
        return $this->sess('trip') && $this->sess('applicant') && $this->sess('calc')
            && count($this->sess('travelers', [])) === $this->expectedTravelers()
            && count($this->sess('calc.travelers', [])) === $this->expectedTravelers();
    }

    private function expectedTravelers(): int
    {
        return count($this->sess('trip.birthdates', []));
    }

    private function isAdult(string $birthDate): bool
    {
        return $birthDate !== '' && Carbon::parse($birthDate)->addYears(18)->lte(today());
    }

    /** Our text for the codes a client can act on, else the API's own (it names the field) */
    private function errorMessage(TravelApiException $e): string
    {
        if ($e->result === null) {
            return __t('messages.flow.insurer_unavailable');
        }

        $key = 'messages.travel.errors.' . $e->result;
        $own = __t($key);

        return $own !== $key ? $own : $e->getMessage();
    }

    /** Y-m-d (or DD.MM.YYYY) → DD.MM.YYYY; unparsable → null */
    private static function apiDate(?string $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            return Carbon::parse(str_replace('.', '-', $date))->format('d.m.Y');
        } catch (\Carbon\Exceptions\InvalidFormatException) {
            return null;
        }
    }

    // ─── Private: view data ───────────────────────────────────────────────────

    /** [id => name] in the current locale, sorted by name */
    private function countryOptions(array $countries): array
    {
        $field   = 'name_' . getCurrentLocale();
        $options = array_map(fn (array $c): array => ['id' => $c['id'], 'name' => $c[$field] ?: $c['name_ru'], 'schengen' => $c['schengen']], $countries);

        uasort($options, fn (array $a, array $b): int => strcoll($a['name'], $b['name']));

        return $options;
    }

    /** [id => name] of the insurer's regions (Latin for uz / en) */
    private function regionOptions(): array
    {
        try {
            $regions = $this->api->regions();
        } catch (ProviderException) {
            return [];
        }

        $field = getCurrentLocale() === 'ru' ? 'name_ru' : 'name_uz';

        return array_map(fn (array $r): string => $r[$field] ?: $r['name_ru'], $regions);
    }

    private function countryNames(array $ids): string
    {
        try {
            $countries = $this->api->countries();
        } catch (ProviderException) {
            return implode(', ', $ids);
        }

        $field = 'name_' . getCurrentLocale();

        return implode(', ', array_map(fn (int $id): string => $countries[$id][$field] ?? (string) $id, $ids));
    }

    private function flowViewData(array $extra = []): array
    {
        $locale    = getCurrentLocale();
        $trip      = $this->sess('trip');
        $calc      = $this->sess('calc') ?? $this->sess('quote');
        $applicant = $this->sess('applicant');
        $travelers = $this->sess('travelers', []);

        $tripUrl      = route('travel.index', ['locale' => $locale]);
        $applicantUrl = route('travel.getApplicant', ['locale' => $locale]);
        $travelersUrl = route('travel.getTravelers', ['locale' => $locale]);

        $period = $calc ? $calc['start_date'] . ' – ' . $calc['policy_end_date'] : null;
        $premium = $calc ? (int) round($calc['insurance_premium']) : null;
        $countries = $trip ? $this->countryNames($trip['countries']) : null;

        $travelerRows = [];
        foreach ($travelers as $i => $t) {
            $name = ($i + 1) . '. ' . trim($t['lastname'] . ' ' . $t['firstname']);
            $uzs  = $calc['travelers'][$i]['premium_uzs'] ?? null;
            $travelerRows[$name] = Carbon::parse($t['birth_date'])->format('d.m.Y') . ($uzs !== null ? ' · ' . formatMoney((int) round($uzs)) : '');
        }

        return array_merge([
            'flow'          => self::FLOW,
            'trip'          => $trip,
            'calc'          => $calc,
            'applicant'     => $applicant,
            'applicantName' => $applicant ? trim($applicant['lastname'] . ' ' . $applicant['firstname'] . ' ' . ($applicant['middlename'] ?? '')) : null,
            'travelers'     => $travelers,
            'expected'      => $trip ? count($trip['birthdates']) : 0,
            'premiumTotal'  => $premium,
            'flowSteps'     => [
                __t('messages.travel.step_trip'),
                __t('messages.flow.applicant'),
                __t('messages.travel.step_travelers'),
                __t('messages.confirm_details'),
                __t('messages.flow.payment'),
            ],
            'flowUrls'      => [$tripUrl, $applicantUrl, $travelersUrl, route('travel.getConfirm', ['locale' => $locale])],
            'summaryItems'  => [
                'countries' => [__t('messages.travel.countries'), $countries],
                'program'   => [__t('messages.travel.program'), $calc ? $calc['program_name'] : null],
                'travelers' => [__t('messages.travel.step_travelers'), $trip ? __t('messages.flow.persons_count', ['count' => count($trip['birthdates'])]) : null],
                'period'    => [__t('messages.flow.period'), $period],
            ],
            'applicantEditUrl' => $applicantUrl,
            'applicantReview'  => $applicant ? [
                __('messages.full_name')                                                  => trim($applicant['lastname'] . ' ' . $applicant['firstname'] . ' ' . ($applicant['middlename'] ?? '')),
                __('insurance.passport.series') . ' / ' . __('insurance.passport.number') => $applicant['passport_seria'] . ' ' . $applicant['passport_number'],
                __t('messages.flow.pinfl')                                                => $applicant['pinfl'],
                __('messages.phone_number')                                               => formatPhone($applicant['phone']),
                __t('messages.travel.address')                                            => $applicant['address'],
            ] : [],
            'confirmBlocks' => [
                ['title' => __t('messages.travel.step_trip'), 'editUrl' => $tripUrl, 'items' => $calc ? [
                    __t('messages.travel.countries')       => $countries,
                    __t('messages.travel.policy_type')     => __t('messages.travel.policy_types.' . $trip['policy_type'])
                        . ($trip['policy_type'] === 1 ? ' · ' . __t('messages.travel.multi_days.' . $trip['multi_days']) : ''),
                    __t('messages.travel.trip_dates')      => $calc['start_date'] . ($calc['end_date'] ? ' – ' . $calc['end_date'] : ''),
                    __t('messages.travel.policy_period')   => $period . ($calc['is_schengen'] && $trip['policy_type'] === 0 ? ' (' . __t('messages.travel.schengen_plus') . ')' : ''),
                    __t('messages.travel.purpose')         => __t('messages.travel.purposes.' . $trip['trip_purpose']),
                    __t('messages.travel.program')         => $calc['program_name'] . ' · ' . number_format($calc['program_liability'], 0, '.', ' ') . ' €',
                ] : []],
                ['title' => __t('messages.travel.step_travelers'), 'editUrl' => $travelersUrl, 'items' => $travelerRows, 'wide' => true],
                ['title' => __t('messages.flow.policy_terms'), 'editUrl' => null, 'items' => $calc ? [
                    __t('messages.travel.premium_usd')   => number_format($calc['premium_usd'], 2, '.', ' ') . ' $',
                    __t('messages.travel.rate')          => number_format($calc['exchange_rate'], 2, '.', ' ') . ' ' . __t('messages.currency'),
                    __('messages.insurance_premium')     => formatMoney($premium),
                ] : []],
            ],
        ], $extra);
    }
}
