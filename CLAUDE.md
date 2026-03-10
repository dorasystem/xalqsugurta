# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Important Constraints

- **PHP is NOT available in the terminal** — do not run `php artisan` commands directly. Use Docker if needed.
- The app runs via Docker: `docker-compose up -d` (app at port 7080, phpMyAdmin at 7081, redis-commander at 7082)

## Commands

```bash
# Frontend
npm run dev          # Vite dev server
npm run build        # Production build

# Composer
composer dev         # Runs server + queue + logs + Vite in parallel (dev only)
composer test        # PHPUnit tests

# Docker
docker-compose up -d
docker-compose logs -f
```

## Architecture Overview

Laravel 12 multi-locale insurance platform. All routes are prefixed by locale: `/{locale}/...` where locale ∈ `{ru, uz, en}`. Helper `getCurrentLocale()` available globally.

### Insurance Products

Five products, each following a **multi-step PRG (Post-Redirect-Get)** pattern with session-based state:

| Product | Controller | Session prefix | Steps |
|---------|------------|----------------|-------|
| OSGOP (carrier liability) | `OsgopController` | `osgop.*` | applicant → vehicle → calculate → confirm |
| OSGOR (employer liability) | `OsgorController` | `osgor.*` | applicant → calculator → confirm |
| Accident | `AccidentController` | `accident.*` | applicant → persons → calculator → confirm |
| Property | `PropertyController` | `property.*` | applicant → owner → property → confirm |
| Gas Balloon | `GasBallonController` | `gas.*` | applicant → property → confirm |

All insurance controllers extend `BaseInsuranceController` (`app/Http/Controllers/Insurence/BaseInsuranceController.php`), which provides:
- `sess($key)` / `putSess($key, $value)` / `clearSess()` — session helpers prefixed by product key
- `normalizePerson(array $p)` — normalizes API person data to consistent fields
- `cleanPhone(?string $phone)` — normalizes Uzbekistan phone numbers to `998XXXXXXXXX`
- `requireSession(string $key, string $redirectRoute)` — guard that redirects if session missing
- `createOrderAndRedirect(array $data, string $productKey)` — creates Order and redirects to payment

### API Layer

**`app/Services/Provider/ProviderApiTrait.php`** — all external API calls go through here:
- `providerRequest(method, param, body)` — base HTTP call with Basic Auth, throws `ProviderException` on error
- `calcRequest(url, body)` — checks `result === 0`, returns `$data['policies'][0]`
- `findPersonByPassport(document, birthDate)` — passport lookup
- `findOrganizationByInn(inn)` — organization lookup
- `submitXalqSugurta(body)` — unified Gas + Property submission (accepts result=302)
- `submitOsgop`, `submitOsgor`, `submitAccident` — product-specific submissions

**`app/Services/OrderService.php`** — creates/updates `Order` records in DB.

### Config

`config/provider.php` — all API credentials and URLs (never hardcode, always use `config('provider.*')`):
- `base_url`, `username`, `password`, `sender_pinfl`, `agency_id`
- `calc.osgop`, `calc.osgor` — calculator endpoints
- `submit.osgop`, `submit.osgor`, `submit.accident` — submission endpoints
- `xalq.*` — Xalq Sugurta API (gas balloon & property), `loan_type.gas=35`, `loan_type.property=36`

### Xalq Sugurta API body format (Gas=35, Property=36)
```php
['customer' => ['address', 'birth_date'(DD.MM.YYYY), 'full_name', 'gender'(int 1/2), 'passport', 'phone', 'pinfl'],
 'loan_info' => ['contract_date'(DD.MM.YYYY), 'contract_number'(uniqid), 'e_date', 'loan_amount'(int),
                 'loan_type'('35'|'36'), 'object_name', 's_date'(DD.MM.YYYY)],
 'subject' => 'P']
```

### Frontend

Bootstrap 5 + Tailwind CSS 4 hybrid: Bootstrap for grid/layout, Tailwind for visual styling. Icons are **Bootstrap Icons** (`bi-*` classes), not FontAwesome.

Blade components under `x-insurence.*` namespace (`resources/views/components/insurence/`):
- `x-insurence.page-header` — props: `icon` (bi-* class), `title`, `subtitle`
- `x-insurence.insurance-sidebar` — props: `title`, `description`, `insuranceSum`, `insurancePremium`; JS targets `#sidebar_sum`, `#sidebar_premium`
- `x-insurence.error-block` — validation error display
- `x-insurence.multi-step-stepper` — step progress indicator

### Premium Rates (client-side calculation)
- Accident: 0.3% of sum_insured per person
- Property: 0.2% of insurance_amount
- Gas Balloon: 0.5% of insurance_amount
- OSGOR/OSGOP: calculated by API

### normalizePerson() output fields
`pinfl`, `passport_seria`, `passport_number`, `birth_date`, `lastname`, `firstname`, `middlename`, `gender` (m/f), `address`, `region_id`, `district_id`, `phone`, `resident_type=1`, `country_id=210`

Note: controllers override `gender` to `'1'`/`'2'` (int string) for API calls after `normalizePerson()`.

### Payment Flow
After order creation: redirect to `route('payment.show', ['locale', 'orderId'])`. Order statuses: `new`, `pending`, `paid`, `cancelled`, `failed`.

### Translations
Three locales: `en`, `ru`, `uz` in `resources/lang/{locale}/`. Key files: `insurance.php`, `messages.php`. Use `__t()` helper (defined in `app/helpers.php`) instead of `__()` to respect the URL locale.

---

## Laravel Code Rules

### Controllers

**New insurance product controllers MUST extend `BaseInsuranceController`**, not `Controller` directly:
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

Use `sess()`, `putSess()`, `clearSess()` — never access `session()` directly in insurance controllers.

**OSGOP is the exception** — it does not extend `BaseInsuranceController` (uses its own `cleanPhone()` and manages session with `self::SESSION_KEY` directly). Do not refactor this.

Controllers must be `final` unless designed for inheritance.

Inject dependencies via constructor, using `readonly`:
```php
public function __construct(
    private readonly FooService $fooService,
    OrderService $orderService,  // pass to parent
) { parent::__construct($orderService); }
```

Keep controllers thin: delegate business logic to Services, API calls to ProviderApiTrait.

### Routes

All insurance routes must include `['locale' => getCurrentLocale()]` in every `redirect()->route()` call:
```php
return redirect()->route('foo.index', ['locale' => getCurrentLocale()]);
```

Route files live in `routes/insurence/` and are included from `routes/web.php`. Name all routes — never use raw URLs.

### Session (Multi-step PRG)

Every step must guard against missing session and redirect back to the start:
```php
// GET handler — always validate prior steps
public function getStep2(): View|RedirectResponse
{
    $applicant = $this->sess('applicant');
    if (!$applicant) {
        return redirect()->route('foo.index', ['locale' => getCurrentLocale()]);
    }
    return view('pages.insurence.foo.step2', compact('applicant'));
}

// POST handler — re-validate session before writing new data
public function storeStep2(Request $request): RedirectResponse
{
    if (!$this->sess('applicant')) {
        return redirect()->route('foo.index', ['locale' => getCurrentLocale()]);
    }
    // ... validate, process, putSess(), then redirect
    return redirect()->route('foo.getStep3', ['locale' => getCurrentLocale()]);
}
```

Never use `redirect()->back()` after a successful POST — always redirect to the next named route (PRG pattern).

### API Calls (ProviderApiTrait)

Always wrap `ProviderApiTrait` calls in try/catch:
```php
try {
    $person = $this->findPersonByPassport($document, $birthDate);
} catch (ProviderException $e) {
    return back()->withErrors(['passport_seria' => __('messages.person_not_found')])->withInput();
}
```

For AJAX endpoints return JSON errors:
```php
} catch (ProviderException $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
}
```

Never catch generic `\Exception` — only `ProviderException`.

### Validation

For simple inline validation, use `$request->validate([...])` directly in the controller method.

For complex or reusable validation (multiple fields, conditional rules), create a `FormRequest` in `app/Http/Requests/Insurence/`. Always `return true` in `authorize()`. Always provide localized `messages()` using `__()` keys from `messages.php`.

Phone regex must be: `'regex:/^998[0-9]{9}$/'`. Passport seria: `'max:4'`, passport number: `'digits:7'`.

### Dates

The provider API requires dates in `DD.MM.YYYY` format. Always use a private helper:
```php
private function toApiDate(string $date): string
{
    return Carbon::parse($date)->format('d.m.Y');
}
```

Store dates internally as `Y-m-d`. Never store `DD.MM.YYYY` in session.

### Order Creation

Always use `createOrderAndRedirect()` from `BaseInsuranceController` (not manual `OrderService::createOrder`) in controllers that extend it. Required fields:
```php
return $this->createOrderAndRedirect([
    'product_name'             => __('insurance.foo.product_name'),
    'amount'                   => $calculation['insurance_premium'],  // integer, in UZS
    'insurance_id'             => (string) $insuranceId,
    'phone'                    => $applicant['phone'],                // 998XXXXXXXXX
    'insurances_data'          => [...],
    'insurances_response_data' => $apiResponse,
    'contractStartDate'        => $calculation['start_date'],         // Y-m-d
    'contractEndDate'          => $calculation['end_date'],           // Y-m-d
    'insuranceProductName'     => __('insurance.foo.product_name'),
], self::SESSION_KEY);
```

### Gender Handling

`normalizePerson()` returns `gender` as `'m'`/`'f'`. Controllers must override this to `'1'`/`'2'` for API submission:
```php
$applicant = array_merge($this->normalizePerson($person, $request), [
    'gender' => ($person['gender'] ?? '') == '1' ? '1' : '2',
]);
```

The Xalq Sugurta API requires `gender` cast to `int`: `'gender' => (int) $applicant['gender']`.

### Blade Views

Always extend `layouts.app`. Use the `x-insurence.*` components — do not duplicate their markup inline.

```blade
<x-insurence.page-header icon="bi-fire" :title="__('insurance.gas.title')" :subtitle="__('insurance.gas.subtitle')" />
<x-insurence.error-block :errors="$errors" />
<x-insurence.insurance-sidebar ... />
```

Always add new translation keys to all three locale files (`en`, `ru`, `uz`) simultaneously.

### Logging

Log significant events (order created, API submission) with `Log::info()`. Log API errors with `Log::error()` inside ProviderApiTrait (not in controllers).
```php
Log::info('Gas balloon order created', ['insurance_id' => $insuranceId]);
```

### Code Style

- Use `??` null coalescing, `??=` null coalescing assignment
- Use `str_starts_with()` / `str_ends_with()` (PHP 8+) — not `strpos()`
- Align array `=>` with spaces for readability in multi-line arrays
- Use named arguments only when it improves clarity
- Section comments with `// ─── Section Name ──...` (Unicode box-drawing dashes) matching the style in existing controllers
