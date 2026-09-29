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

Eight products, each following a **multi-step PRG (Post-Redirect-Get)** pattern with session-based state:

| Product | Controller | Session prefix | Steps |
|---------|------------|----------------|-------|
| OSAGO (motor liability) | `OsagoController` | `osago.*` | vehicle → owner + applicant → term + drivers → confirm |
| OSGOP (carrier liability) | `OsgopController` | `osgop.*` | applicant (person or organization) → vehicle → term → confirm |
| OSGOR (employer liability) | `OsgorController` | `osgor.*` | applicant → calculator → confirm |
| Accident | `AccidentController` | `accident.*` | applicant → persons → term (calculator route) → confirm |
| Tourist | `TouristController` | `tourist.*` | same as Accident (product code 203) |
| Property | `PropertyController` | `property.*` | applicant → property → confirm |
| Gas Balloon | `GasBallonController` | `gas.*` | applicant → property → confirm |
| KASKO | `KaskoController` | `kasko.*` | applicant → vehicle → confirm |

All insurance controllers extend `BaseInsuranceController` (`app/Http/Controllers/Insurence/BaseInsuranceController.php`), which provides:
- `sess($key)` / `putSess($key, $value)` / `clearSess()` — session helpers prefixed by product key
- `normalizePerson(array $p)` — normalizes API person data to consistent fields
- `cleanPhone(?string $phone)` — normalizes Uzbekistan phone numbers to `998XXXXXXXXX`
- `requireSession(string $key, string $redirectRoute)` — guard that redirects if session missing
- `createOrderAndRedirect(array $data, string $productKey)` — creates Order and redirects to payment

### API Layer

**`app/Services/Provider/ProviderApiTrait.php`** — all external API calls go through here:
- `providerRequest(method, param, body)` — registry proxy call (person, vehicle, INN, cadaster) with Basic Auth, throws `ProviderException` on error
- `insurerPost(url, body, timeout, retries, auth)` — every calculator / sale POST goes through it
- `ProviderException::isUnavailable()` (code ≥ 500: timeout, 5xx, or the proxy's `error: 503`) means an outage, not "not found". Controllers show `messages.flow.registry_unavailable` for lookups (`BaseInsuranceController::lookupErrorMessage()`) and `messages.flow.insurer_unavailable` for calc/sale (`providerErrorMessage()`); otherwise the insurer's own message. Request bodies (passport data) never go to the app log — they are in the API jurnali
- `calcRequest(url, body)` — checks `result === 0`, returns `$data['policies'][0]`
- `findPersonByPinfl(pinfl, document)` — person lookup by PINFL + passport (used by all migrated flows)
- `findPersonByPassport(document, birthDate)` — legacy passport + birth date lookup (no longer used by any product flow)
- `findOrganizationByInn(inn)` — organization lookup
- `submitXalqSugurta(body)` — unified Gas + Property submission (accepts result=302)
- `submitOsgop`, `submitOsgor`, `submitAccident(body, ?url)` — product-specific submissions; `calculatePersonsInsurance(productCode, sum, start, termMonths)` — accident/tourist calculator

**`app/Services/OrderService.php`** — creates/updates `Order` records in DB.

### Config

Full provider API reference (endpoints, bodies, auth groups, open questions): `docs/API.md`.

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

Blade components live under the `x-insurence.*` namespace (`resources/views/components/insurence/`); the old page-header / insurance-sidebar / error-block / stepper components were removed with the last unmigrated pages.

Header/footer (`components/pageComponents/{header,footer}`) keep the old markup and `main.js`; `public/assets/css/site-chrome.css` (loaded after `main.min.css`) restyles them in the flow look. The credit is `components/pageComponents/dora-credit` (DORA, dora.uz, animated SVG mark) in the footer and the mobile menu.
Search: the header's search button opens a small form (`.site-search`, script at the end of the header partial) that goes to `/{locale}/search` (`SearchController`, view `pages/search`): active products (by name in any locale or description), the service pages (my policies, claims, callback) and published company pages; apostrophe kinds (‘ ’ ʻ `) are treated alike. Strings: `messages.site_search.*` (`messages.search` is an older plain string).

**Unified flow standard** (`public/assets/css/flow.css`, brand color `#393185`). All products (gas balloon, property, KASKO, accident, tourist, OSGOR, OSGOP, OSAGO) and the payment page use it; new pages must too:
- `x-insurence.flow` — page frame: header, stepper, main slot + `summary` slot. Props: `icon`, `title`, `subtitle`, `steps` (labels), `current` (1-based), `stepUrls`. Shows `$errors->first('error')` as an alert.
- `x-insurence.field` — label + input + help/inline error. Props: `name`, `label`, `type`, `value`, `help`, `id`; extra attributes go to the `<input>`; optional `append` slot.
- `x-insurence.found` — green "found in database" block (`title`, `text`); JS updates `[data-found="title|text"]`.
- `x-insurence.summary` — "your policy" sidebar. Props: `premium` (int|null), `rate`, `items` (`key => [label, value|null]`); JS targets `#sidebar_premium`, `[data-summary="key"]`.
- `x-insurence.review` — confirm-page block with "Edit" link. Props: `title`, `editUrl`, `items` (`label => value`).
- `x-insurence.actions` — back link + primary submit; becomes a sticky bar with the total on phones. Props: `backUrl`, `submit`, `total`.
- "applicant → object + sum → confirm" products share `pages/insurence/flow/{applicant,confirm}` and the `Concerns\InsuranceFlow` trait (`flowViewData()`, `premiumFor()`). Each controller defines a `FLOW` constant (key, icon, rate, rateLabel, min, max, default, presets, optional step, objectKey, objectStep, objectTitle) and implements `objectLabel()` / `objectReview()` for its insured object.
- Cadastral numbers: `XX:XX:XX:XX:XX:XXXX` then blocks after `:` or `/` (`…:XXXX:XXXX:XXX`, `…:XXXX/XXXX`). Never put a real cadastral number in code, examples or tests (it identifies a home); use obviously fake ones like `11:11:11:11:11:1111:2222:333`. The input is masked by `formatCadaster()` in `cadaster/property`; the server checks `CadasterFlow::cadasterRule()` (same regex) on the AJAX lookup (`cadasterLookup()`, shared by gas/property) and on step 2. `PropertyService` returns `reason` = `not_found` | `unavailable`, shown as `messages.flow.cadaster_not_found` / `cadaster_unavailable`. Don't add region-code or object-type rules without an official source.
- Step 2 pages: gas/property use `cadaster/property` + `Concerns\CadasterFlow` (adds `cadasterRoute`); KASKO uses `kasko/vehicle`. Both include `flow/partials/sum-dates` and `flow/partials/calc-script` (`window.xfCalc.reveal(label)` after the object lookup). The slider `step` must divide `value - min`, or the browser snaps the amount (KASKO uses step 1 mln).
- Person lookup: `BaseInsuranceController::applicantFromRequest()` (step 1) and `findPerson()` (AJAX) take passport + `pinfl` (14 digits); `birth_date` is still accepted for unmigrated pages. Birth date comes from the API's `birthDate`, else is decoded from the PINFL (digits 2–7 = DDMMYY, first digit = century/gender).
- Accident and tourist extend the abstract `PersonsInsuranceController` (uses `Concerns\PersonsFlow`: applicant → persons → term → confirm, views `persons/{persons,term}`); the subclasses only define FLOW, the session key and `productCode()` (202 / 203). Premiums per person come from `ProviderApiTrait::calculatePersonsInsurance()`; the sale goes to `provider.submit.{key}` via `submitAccident()`.
- `flow/confirm` renders `$confirmBlocks` ([title, editUrl, items]) and `$premiumTotal` supplied by the flow trait; `$applicantTitle` / `$applicantReview` replace the person block (OSGOR shows the organization).
- OSAGO (`OsagoController`, views `osago/{vehicle,owner,terms}` + `flow/confirm`): plate + tech passport → owner (+ applicant if different) by passport + PINFL → start date + unlimited / named drivers (≤ 5, added by `addDriver` with `driver-summary-v2`) → confirm → `InsuranceApiService` (`doraosago/create`). The premium is `OsagoPriceCalculator` on the registry vehicle's `vehicleTypeId` and plate, recalculated in `storeApplication()`; nothing price-related is read from the request. Only the 12-month term is sold. Organization owners (INN → `findOrganizationByInn`, the organization is also the applicant; body fills `owner.organization.inn` + `applicant.organization`, person blocks empty) are sold only while `provider.osago.legal_entities` is on (admin: Tizim → Sug'urtachi API, `ProviderSettings::SWITCHES`); it is off by default because the insurer gave no sample body — `storeApplication()` sends an organization back to the owner step if it was switched off meanwhile. The old public registry proxies (`/get-person-info`, `/get-vehicle-info`, `/get-driver-info`, `/get-company-info`) were removed; lookups go through the product controllers. `/osago/payment/{order}` redirects to `payment.show`.
- OSGOR (`OsgorController`, views `osgor/{organization,calculator}` + `flow/confirm`): INN + contact phone → salary fund (FOT) + start date → confirm. The premium is previewed by `osgor.calculate` and recalculated on the server in `storeCalculation()`; never accept premium/rate/sum from the request. Its view data comes from the controller's own `flowViewData()` (no FLOW settings: priced by the insurer's API).
- **Admin-configurable numbers**: `products.settings` (JSON) overrides FLOW per product — rate, min_premium, min/max/default/step, presets, term_months, start_offset (0 today / 1 tomorrow), max_start_days. `App\Services\ProductSettings` merges (`merge()`/`effective()`), validates admin input (`clean()`, cross-checks step vs default/presets) and holds `premium()`, `startDateRules()`, `endDate()`. Controllers read `$this->flow()` (trait `Concerns\ConfigurableFlow`, pulled in by InsuranceFlow/PersonsFlow) — never `self::FLOW` directly; FLOW constants are `public` so ProductSettings can read the built-in defaults. Supported products: `ProductSettings::CONTROLLERS`.
- Offerta checkbox lives on the confirm step (validated in `storeApplication`), not step 1.
- Helpers: `formatMoney($uzs)` → `250 000 so'm`, `formatPhone($p)` → `+998 90 123 45 67`. Flow strings are under `messages.flow.*`.

### Admin panel (Filament 4, `/admin`)

`app/Providers/Filament/AdminPanelProvider.php` — brand palette (`#393185` = primary 600), site logo/favicon, Inter/Gilroy fonts. Filament ships precompiled CSS, so brand tweaks live in plain CSS `public/assets/css/admin.css` (targets `fi-*` classes, injected via `PanelsRenderHook::HEAD_END`); Tailwind classes added in PHP will not exist. UI is Uzbek-only: `SetAdminLocale` (persistent middleware) sets `uz` and Carbon `uz_Latn`.
- Access: `User` implements `FilamentUser`; `canAccessPanel()` allows emails in `ADMIN_EMAILS` (comma-separated, `config('app.admin_emails')`), or every user when it is empty. Without this Filament returns 403 whenever `APP_ENV` is not `local`.
- Users: `UserResource` (group "Tizim") creates/edits admin users; password min 10 chars with letters + digits, left empty on edit = unchanged (the `hashed` cast hashes it). Nobody can delete themselves (`UserResource::isDeletable()`); editing your own password refreshes the session hash so you stay logged in. The "Panelga kira oladi" column shows who `ADMIN_EMAILS` lets in.
- Prod nginx must pass unknown `.js` to Laravel (`try_files` in `docker/nginx/prod.default.conf`), or `/livewire/livewire.min.js` 404s and the login form does nothing.
- Dashboard (`Filament/Admin/Pages/Dashboard`, 3 columns): `OrdersStatsOverview`, `RevenueChart`, `ProductSalesChart`, `LatestOrders` widgets. Date grouping is done in PHP so it works on MySQL and SQLite.
- `OrderResource::columns()` is shared by the list page and `LatestOrders`. Status labels/colors come from `Order::STATUS_LABELS` / `Order::statusColor()`; `Order::client_name` and `Order::applicant` read the applicant from `insurances_data` (person, organization, or OSGOP's nested shape).
- `ProductResource` form uses UZ/RU/EN tabs; `route` options come from `Product::CATEGORIES`. "Narx va chegaralar" section edits `settings.*` (saved through `ProductResource::withCleanSettings()`); `SettingChangesRelationManager` lists `product_setting_changes`, written by `Product::recordSettingChanges()` on every settings / `is_active` change.
- **API jurnali**: `App\Services\ApiLogger` (registered in `AppServiceProvider`) listens to the HTTP client events and writes every request to the insurer's hosts to `api_logs` (`ApiLog`, prunable after 30 days via `model:prune` in `routes/console.php`; each retry attempt is a row; the Authorization header is never stored; `request_raw` keeps the body byte for byte — the `request` JSON column is re-sorted by MySQL and loses `\uXXXX` escapes, so copy / compare with `ApiLog::exactRequest()`). Rows made before an order exists are linked by `ApiLogger::attachToOrder()` in `createOrderAndRedirect()`; wrap calls for a known order in `ApiLogger::forOrder()`. Logging swallows its own errors and never writes to the app log. `ApiLogResource` (group "Nazorat") lists them; `OrderResource` shows them per order (`ApiLogsRelationManager`) plus the "Jarayon" timeline (`OrderResource::timeline()`, view `filament/admin/order-timeline`).
- Policies of gas/property/KASKO (`Order::POLICY_AFTER_PAYMENT`) come from `PerformTransactionRequest` after payment; `Order::awaitsPolicy()` / `scopeAwaitingPolicy()` find paid orders without `download_url`. The order card action "Polisni qayta so'rash" re-sends it through `XalqPolicyService` (same `ConfirmPayment` code path). Dashboard `AttentionWidget` lists these, today's API errors and payments waiting > 2 h.
- `is_active` ("Sotuvda") hides the card and closes the product's routes via the `product.on-sale:{route}` middleware (`EnsureProductOnSale`); old `/osago/payment/{order}` links stay open.

### Premium Rates (built-in defaults; admins can change them per product)
- Property: 0.2% of insurance_amount
- Gas Balloon: 0.5% of insurance_amount
- KASKO: 3% of insurance_amount
- Accident, OSGOR/OSGOP: calculated by API

### normalizePerson() output fields
`pinfl`, `passport_seria`, `passport_number`, `birth_date`, `lastname`, `firstname`, `middlename`, `gender` (m/f), `address`, `region_id`, `district_id`, `phone`, `resident_type=1`, `country_id=210`

Note: controllers override `gender` to `'1'`/`'2'` (int string) for API calls after `normalizePerson()`.

### Payment Flow
After order creation: redirect to `route('payment.show', ['locale', 'orderId'])`. Order statuses: `new`, `pending`, `paid`, `cancelled`, `failed`.
- The payment page (`PaymentController::show`, view `pages/insurence/payment`, unified flow design) has four states: `pay`, `policy_pending` (paid, policy not issued yet — reloads every 15 s), `paid`, `cancelled`. Order ids are sequential, so the phone, period and policy links are shown only when `OrderService::canSeeDetails()` passes: the order id is in the session list `OrderService::SESSION_ORDERS` (filled by `createOrder()`) or an admin is signed in.
- **Click** (`routes/api.php`: `/api/prepare`, `/api/complete`): `App\Services\Payments\ClickShopApi` implements the SHOP API — MD5 `sign_string` check with `CLICK_SECRET_KEY` (every request is rejected with -1 while it is empty), amount check, idempotent Prepare, error codes -1…-9 (docs.click.uz/en/shop-api). A `click_uzs` row is one transaction; its id is `merchant_prepare_id`. A successful Complete marks the order paid and requests the gas/property/KASKO policy after the response (`dispatch(...)->afterResponse()`), since PerformTransactionRequest can outlast Click's timeout. `tests/Feature/ClickShopApiTest` mirrors Click's 15 Postman scenarios.
- **Payme** (`PaymeController::PerformTransaction`) and Click Complete both call `InsurerConfirmation::afterResponse()`: gas/property/KASKO → `XalqPolicyService` (PerformTransactionRequest), eshop contracts (`Order::ESHOP_PAYMENT_CONFIRM`: OSGOP, OSGOR, accident, tourist) → `EshopPaymentService` (`eshop/payment`, same body; sets `insurances_response_data.payment_confirmed_at`). Admin: order card "To'lovni tasdiqlash", tab "To'lov tasdiqlanmagan", `AttentionWidget`. Old orders without `_product_key` still go through `ConfirmPayment::confirmXalqSugurtaPayment()`.
- **Payment settings in the admin panel** (Tizim → To'lov tizimlari, `Filament/Admin/Pages/PaymentSystems`): `App\Services\PaymentSettings` stores Click (enabled, service/merchant/merchant-user id, secret) and Payme (enabled, cashbox id, keys, test mode) in `app_settings` (secrets encrypted with APP_KEY) and copies them over `config('services.click.*' / 'services.payme.*')` in `AppServiceProvider::boot()` (cached; `save()` clears it). Empty fields fall back to .env; an empty secret field keeps the saved key. Keep reading `config()` in payment code. The payment page shows Click when the insurer gave a `click_url` or `PaymentSettings::clickReady()`, Payme when it gave a `payme_url` or `paymeEnabled()`.

- **Insurer API settings in the admin panel** (Tizim → Sug'urtachi API, `Filament/Admin/Pages/ProviderApiSettings`): `App\Services\ProviderSettings` stores `provider.agency_id` (the OSGOP/OSGOR `agencyId`) and the OSAGO legal-entity switch in `app_settings` and applies them over config at boot; an empty text field = .env, switches are saved as '1'/'0' so "off" beats an .env "on". The insurer rejects an agency that is not its own ("The selected agency does not belong to your insurance organization").

- **Site settings** (Tizim → Sayt sozlamalari, `Filament/Admin/Pages/SiteSettingsPage`): `App\Services\SiteSettings` keeps the Instagram / Facebook / Telegram links in `app_settings`; `SiteSettings::social()` returns only filled ones and the layout / mobile menu show only those icons (the old site's links were `#`; don't invent account URLs).

### Home page
- `HomeController` → `welcome.blade.php` + `public/assets/css/home.css` (prefix `hp-`, display font Unbounded loaded on this page only through `@stack('head')`). Sections: dark hero with a sample e-policy (its price is `OsagoPriceCalculator` for 01A123BC, limited drivers), quick quote tabs (OSAGO form GETs `osago.index?gov_number=…` and step 1 prefills it; other tabs link to the application), figures, product bento (OSAGO tile with the cheapest car price, rate-priced products show `ProductSettings` rate, OSGOR/OSGOP in the wide "business" tile, last-row tiles stretch), three steps, claims, self-service, FAQ + disclosure links (`InfoPage::link`), sticky actions on phones.
- Figures are admin-editable (Tizim → Sayt sozlamalari → "Bosh sahifa raqamlari", `SiteSettings::stats()`): a slot never saved shows `messages.homepage.stats`, one saved with an empty value is hidden. FAQ answers only describe how the site works (no insurance terms). Strings: `messages.homepage.*` (`messages.home` is an older plain string).

### Product info pages
- `/{locale}/products/{route}` (`ProductPageController`, view `pages/product/show`): texts from `products.content` (`{uz|ru|en: {about, claim, faq: [{q, a}]}}`, edited in ProductResource → each locale tab → "Mahsulot sahifasi") and the rules PDF `rules_{locale}`. `Product::info()` returns only filled parts and passes admin HTML through `Product::safeHtml()` (formatting tags only, no attributes except safe hrefs). A locale with no texts redirects to the application; the home card uses `Product::cardUrl()` (info page when filled, else the application). Insurance terms are the insurer's text — don't write them; the old site's product texts are partly outdated and its FAQ is lorem ipsum.
- `/sitemap.xml` and `/robots.txt` come from `SeoController` (products on sale and published company pages × uz/ru/en, hreflang; payment and my-policies pages disallowed). They use APP_URL.

### Company / disclosure pages (moved from the old site)
- `/{locale}/info/{key}` (`InfoPageController`, view `pages/info/show`): `info_pages` rows (`InfoPage`, title/body per locale, falling back to ru then uz; body cleaned by `App\Support\SafeHtml`, shared with `Product::safeHtml()`). Edited in the admin panel, Katalog → Kompaniya sahifalari (`InfoPageResource`).
- `InfoPage::SOURCES` lists the old xalqsugurta.uz pages (key → section, old path; most paths from `resources/lang/{l}/routes.php`). The header/footer use `InfoPage::link('key')`: our page when published, else the old site, so nothing breaks while staff check the imported text.
- `php artisan site:import-old-pages [--only=a,b] [--download] [--force]` fetches them (`App\Services\Site\OldSitePage` keeps `.template-content` as plain HTML: headings, lists, links; drops images, icons and the side menu). New pages are unpublished; existing ones are skipped unless `--force`. `--download` copies `/uploads/...` documents to the public disk (`info-pages/`) and links `/storage/...`.
- Prod: nginx mounts the `app_storage` volume read-only and the deploy runs `storage:link`, so public-disk files (rules PDFs, page documents) are served.

### Claims and call-back requests (Murojaatlar)
- The insurer has no API for them: they are stored and handled by staff in the admin panel (group "Murojaatlar": `ClaimResource`, `CallbackRequestResource`, navigation badges = new ones).
- `ClaimController`: `/{locale}/claims` (form; `?order=` pre-fills a policy only if `OrderService::canSeeDetails()`; `?product=` preselects), `claims.sent/{number}` (only for the browser that filed it), `claims.status` (number AND phone must match). Numbers `ZH-yymmdd-NNNN` (random). Up to 5 files (jpg/png/pdf, 10 MB) on the private `local` disk under `claims/{id}`; staff open them via `claims.file` — a 30-min signed link generated in `filament/admin/claim-files` + an admin session (`ClaimFileController`). Honeypot field `website`; throttled.
- `CallbackController`: `/{locale}/callback`; one open request per phone (repeats update it). Admin marks "Bog'lanildi".
- `x-insurence.actions :payment="false"` hides the "to pay" total on forms that sell nothing.
- Newsletter: the footer form posts to `newsletter.store` (`NewsletterController`, honeypot, throttled, errors in the `newsletter` bag); emails go to `newsletter_subscribers`, listed and exported as CSV in `NewsletterSubscriberResource`. Nothing is mailed yet.

### "Mening polislarim" (customer self-service)
- `MyPoliciesController` (`/{locale}/my-policies`, views `pages/my-policies/{login,list}`): phone → SMS code → every order with that `phone`. `App\Services\PhoneVerification` sends a 6-digit code (stored hashed, 5 min, 5 wrong tries kill it, resend after 60 s, 3 codes per phone / 30 min, 10 per IP / hour). On success the session id is regenerated and `OrderService::SESSION_PHONE` holds the phone for `PHONE_SESSION_MINUTES`; `OrderService::canSeeDetails()` then also opens the payment page details of that phone's orders.
- SMS: `App\Services\Sms\EskizClient` (notify.eskiz.uz; bearer token cached 25 days, renewed once on 401; throws `SmsException`, never logs the phone or text). Settings in the admin panel, Tizim → SMS xabarlar (`SmsSettings`, password encrypted, `{code}` template must match a template Eskiz approved). `SmsSettings::ready()` false → the page says SMS is unavailable.
- Policy verification against the insurer's whole database is NOT built: the insurer has not given an API for it.

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

**OSGOP is the exception** — it does not extend `BaseInsuranceController` (uses its own `cleanPhone()` and manages session with `self::SESSION_KEY` directly). Do not refactor this. It still uses the unified views: its own `flowViewData()` feeds `osgop/{applicant,vehicle,calculator}` and `flow/confirm`. The premium is computed from the vehicle type and seats of the registry vehicle in the session (`calculation()`), never from request values, and recalculated in `storeCalculation()`. The registry's `vehicleTypeId` is mapped to the OSGOP table (`osgopType()`: registry 1/2 → 2 car; 9 → 1 bus if > 20 seats, else 7 minibus; others refused) — never send the registry id. The carrier licence (seria, number, dates) is typed in on the vehicle step; `submitOsgop()` sends only the owner block that applies.

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
<x-insurence.flow :icon="$flow['icon']" :title="..." :steps="$flowSteps" :current="2" :stepUrls="$flowUrls">
    <form class="xf-panel">…<x-insurence.field … /> … <x-insurence.actions … /></form>
    <x-slot:summary><x-insurence.summary :premium="$premiumTotal" :items="$summaryItems" /></x-slot:summary>
</x-insurence.flow>
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
