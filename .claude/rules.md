# Laravel Code Rules — example-app

## Controllers

New insurance controllers MUST extend `BaseInsuranceController`, not `Controller`:
```php
final class FooController extends BaseInsuranceController
{
    private const SESSION_KEY = 'foo';

    public function __construct(OrderService $orderService) {
        parent::__construct($orderService);
    }

    protected function getProductKey(): string { return self::SESSION_KEY; }
}
```

- Always `final` unless designed for inheritance
- Inject via constructor with `readonly`; pass `$orderService` to `parent::__construct()`
- Session: use `sess()`, `putSess()`, `clearSess()` — never `session()` directly
- OSGOP is the exception — it does NOT extend `BaseInsuranceController`; do not refactor it

## Routes

- Every `redirect()->route()` must include `['locale' => getCurrentLocale()]`
- All route files go in `routes/insurence/`, referenced from `routes/web.php`
- Always use named routes — never raw URLs

## Session / Multi-step PRG

Every GET step must guard missing session and redirect to start:
```php
public function getStep2(): View|RedirectResponse
{
    if (!$this->sess('applicant')) {
        return redirect()->route('foo.index', ['locale' => getCurrentLocale()]);
    }
    return view('pages.insurence.foo.step2');
}
```

Every POST step must re-validate session before writing new data, then redirect to the **next** named route (never `back()` after success).

## API Calls (ProviderApiTrait)

Wrap all trait calls in try/catch — only `ProviderException`, never generic `\Exception`:
```php
try {
    $person = $this->findPersonByPassport($doc, $birthDate);
} catch (ProviderException $e) {
    return back()->withErrors(['passport_seria' => __('messages.person_not_found')])->withInput();
}
```

AJAX endpoints return JSON on error:
```php
} catch (ProviderException $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
}
```

## Validation

- Inline `$request->validate([...])` for simple, single-use validation
- `FormRequest` in `app/Http/Requests/Insurence/` for complex or conditional rules
- `authorize()` always returns `true`
- Custom `messages()` must use `__()` keys from `messages.php`
- Phone: `'regex:/^998[0-9]{9}$/'` — Passport seria: `'max:4'` — Passport number: `'digits:7'`

## Dates

- Store in session as `Y-m-d`
- Provider API requires `DD.MM.YYYY` — always convert via a private helper:
```php
private function toApiDate(string $date): string
{
    return Carbon::parse($date)->format('d.m.Y');
}
```

## Order Creation

Use `createOrderAndRedirect()` (BaseInsuranceController) — not `OrderService` directly:
```php
return $this->createOrderAndRedirect([
    'product_name'             => __('insurance.foo.product_name'),
    'amount'                   => $calculation['insurance_premium'], // int, UZS
    'insurance_id'             => (string) $insuranceId,
    'phone'                    => $applicant['phone'],               // 998XXXXXXXXX
    'insurances_data'          => [...],
    'insurances_response_data' => $apiResponse,
    'contractStartDate'        => $calculation['start_date'],        // Y-m-d
    'contractEndDate'          => $calculation['end_date'],          // Y-m-d
    'insuranceProductName'     => __('insurance.foo.product_name'),
], self::SESSION_KEY);
```

## Gender

`normalizePerson()` returns `'m'`/`'f'`. Override to `'1'`/`'2'` for API:
```php
'gender' => ($person['gender'] ?? '') == '1' ? '1' : '2',
```
Xalq Sugurta API requires int: `'gender' => (int) $applicant['gender']`

## Blade / Views

- Always extend `layouts.app`
- Use `x-insurence.*` components — do not inline their markup
- Icons: Bootstrap Icons only (`bi-*`) — never FontAwesome
- Add translations to **all three** locale files simultaneously (`en`, `ru`, `uz`)

## Logging

- `Log::info()` — significant events (order created, submission sent)
- `Log::error()` — API failures (inside ProviderApiTrait, not controllers)

## Code Style

- PHP 8.2+: use `??`, `??=`, `str_starts_with()`, `str_ends_with()`, named arguments where helpful
- Align array `=>` operators with spaces in multi-line arrays
- Section dividers: `// ─── Section Name ─────...` (Unicode box-drawing dashes, matching existing controllers)
- Config values: always `config('provider.*')` — never hardcode URLs or credentials
- Translations: always `__t()` helper (not `__()`) to respect URL locale
