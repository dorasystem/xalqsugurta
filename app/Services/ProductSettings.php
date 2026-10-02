<?php

namespace App\Services;

use App\Http\Controllers\Insurence\AccidentController;
use App\Http\Controllers\Insurence\GasBallonController;
use App\Http\Controllers\Insurence\KaskoController;
use App\Http\Controllers\Insurence\PropertyController;
use App\Http\Controllers\Insurence\TouristController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Per-product numbers the admin panel can change (stored in products.settings JSON).
 *
 * Each migrated controller keeps its built-in values in a FLOW constant; the admin
 * settings override them key by key, and an empty field falls back to FLOW.
 */
final class ProductSettings
{
    /** Product route => controller whose FLOW constant holds the built-in values */
    public const CONTROLLERS = [
        'gas'      => GasBallonController::class,
        'property' => PropertyController::class,
        'kasko'    => KaskoController::class,
        'accident' => AccidentController::class,
        'tourist'  => TouristController::class,
    ];

    /** Keys the admin can override, with their labels in the (Uzbek-only) admin panel */
    public const LABELS = [
        'rate'           => 'Stavka',
        'min_premium'    => 'Minimal mukofot',
        'min'            => 'Minimal summa',
        'max'            => 'Maksimal summa',
        'default'        => 'Standart summa',
        'step'           => 'Slayder qadami',
        'presets'        => 'Tezkor tanlov tugmalari',
        'term_months'    => 'Shartnoma muddati',
        'start_offset'   => 'Eng erta boshlanish',
        'max_start_days' => 'Eng kech boshlanish',
    ];

    /** Keys that only apply when the premium is sum × rate (not an API calculator) */
    private const RATE_KEYS = ['rate', 'min_premium'];

    private const MONEY_KEYS = ['min_premium', 'min', 'max', 'default', 'step'];

    public const START_OFFSETS = [0 => 'Bugundan', 1 => 'Ertadan'];

    // ─── Lookups ──────────────────────────────────────────────────────────────

    public static function supports(?string $route): bool
    {
        return isset(self::CONTROLLERS[$route]);
    }

    /** True when the site computes the premium itself (sum × rate) */
    public static function hasRate(?string $route): bool
    {
        return self::supports($route) && isset(self::flowOf($route)['rate']);
    }

    /** Keys that apply to this product */
    public static function keys(string $route): array
    {
        $keys = array_keys(self::LABELS);

        return self::hasRate($route) ? $keys : array_values(array_diff($keys, self::RATE_KEYS));
    }

    /** Built-in values (FLOW + defaults for the keys FLOW leaves out) */
    public static function defaults(string $route): array
    {
        return self::supports($route) ? self::withDefaults(self::flowOf($route)) : [];
    }

    private static function flowOf(string $route): array
    {
        $controller = self::CONTROLLERS[$route];

        return $controller::FLOW;
    }

    /** Effective values for a product row (null when the product has no configurable flow) */
    public static function effective(?string $route, ?array $settings): ?array
    {
        return self::supports($route) ? self::merge(self::flowOf($route), $settings) : null;
    }

    public static function label(string $key): string
    {
        return self::LABELS[$key] ?? ($key === 'is_active' ? 'Sotuvda' : $key);
    }

    // ─── Runtime ──────────────────────────────────────────────────────────────

    /**
     * FLOW with the admin overrides applied, plus derived values for the views:
     * rateLabel ('0,5'), start_min / start_max (Y-m-d, start_max may be null).
     */
    public static function merge(array $flow, ?array $settings): array
    {
        $base   = self::withDefaults($flow);
        $merged = self::cast(array_replace($base, self::filled($settings ?? [], array_keys(self::LABELS))));

        // Settings are validated on save; this only guards against values edited in the DB directly
        if (!self::isSane($merged)) {
            $merged = self::cast($base);
        }

        // Keep chips and the default inside the range, and a slider step that lands on all of them
        $merged['default'] = min(max($merged['default'], $merged['min']), $merged['max']);
        $merged['presets'] = array_values(array_unique(array_filter(
            $merged['presets'],
            fn (int $p) => $p >= $merged['min'] && $p <= $merged['max'],
        )));
        sort($merged['presets']);
        $merged['step'] = self::safeStep($merged);

        if (isset($merged['rate'])) {
            $merged['rateLabel'] = rtrim(rtrim(number_format($merged['rate'], 2, ',', ''), '0'), ',');
        }

        $merged['start_min'] = today()->addDays($merged['start_offset'])->format('Y-m-d');
        $merged['start_max'] = $merged['max_start_days'] === null
            ? null
            : today()->addDays(max($merged['max_start_days'], $merged['start_offset']))->format('Y-m-d');

        return $merged;
    }

    /** Premium for a sum on a rate-based product, never below the minimum premium */
    public static function premium(array $flow, int $sum): int
    {
        return max((int) round($sum * $flow['rate'] / 100), (int) ($flow['min_premium'] ?? 0));
    }

    /** Validation rules for the policy start date (Y-m-d) */
    public static function startDateRules(array $flow): array
    {
        return array_filter([
            'required',
            'date',
            'after_or_equal:' . $flow['start_min'],
            $flow['start_max'] ? 'before_or_equal:' . $flow['start_max'] : null,
        ]);
    }

    /** Policy end date (Y-m-d) for a start date: term in months, last day inclusive */
    public static function endDate(array $flow, string $startDate): string
    {
        return \Carbon\Carbon::parse($startDate)->addMonths($flow['term_months'])->subDay()->format('Y-m-d');
    }

    // ─── Admin panel ──────────────────────────────────────────────────────────

    /**
     * Normalizes the admin form input ("5 000 000" → 5000000, empty → dropped) and validates it
     * together with the built-in values. Throws ValidationException keyed "{$errorPrefix}{key}".
     */
    public static function clean(string $route, array $input, string $errorPrefix = ''): array
    {
        if (!self::supports($route)) {
            return [];
        }

        $data = self::filled(self::normalize($input), self::keys($route));

        $validator = Validator::make($data, [
            'rate'           => ['numeric', 'gt:0', 'max:100'],
            'min_premium'    => ['integer', 'min:0'],
            'min'            => ['integer', 'min:1'],
            'max'            => ['integer', 'min:1'],
            'default'        => ['integer', 'min:1'],
            'step'           => ['integer', 'min:1'],
            'presets'        => ['array', 'max:8'],
            'presets.*'      => ['integer', 'min:1'],
            'term_months'    => ['integer', 'between:1,60'],
            'start_offset'   => ['integer', 'in:0,1'],
            'max_start_days' => ['integer', 'between:0,365'],
        ], [], self::LABELS);

        $errors = [];
        foreach ($validator->errors()->messages() as $key => $messages) {
            $errors[explode('.', $key)[0]] ??= $messages[0];
        }

        if (!$errors) {
            $errors = self::crossCheck(self::cast(array_replace(self::defaults($route), $data)));
        }

        if ($errors) {
            throw ValidationException::withMessages(
                collect($errors)->mapWithKeys(fn ($m, $k) => [$errorPrefix . $k => $m])->all()
            );
        }

        return self::cast($data, onlyPresent: true);
    }

    /** Human-readable value for the change history */
    public static function display(string $key, mixed $value): string
    {
        if ($value === null || $value === '' || $value === []) {
            return 'standart';
        }

        return match (true) {
            $key === 'is_active'                => $value ? 'Sotuvda' : 'O\'chirilgan',
            $key === 'rate'                     => str_replace('.', ',', (string) $value) . '%',
            in_array($key, self::MONEY_KEYS)    => formatMoney((int) $value),
            $key === 'presets'                  => collect((array) $value)->map(fn ($p) => self::short((int) $p))->implode(', '),
            $key === 'term_months'              => $value . ' oy',
            $key === 'start_offset'             => self::START_OFFSETS[(int) $value] ?? (string) $value,
            $key === 'max_start_days'           => $value . ' kun',
            default                             => is_scalar($value) ? (string) $value : json_encode($value),
        };
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    private static function withDefaults(array $flow): array
    {
        return array_replace([
            'step'           => 5_000_000,
            'min_premium'    => 0,
            'term_months'    => 12,
            'start_offset'   => 0,
            'max_start_days' => null,
        ], $flow);
    }

    /** Only the given keys, without empty values (empty = use the built-in value) */
    private static function filled(array $settings, array $keys): array
    {
        return array_filter(
            array_intersect_key($settings, array_flip($keys)),
            fn ($v) => $v !== null && $v !== '' && $v !== [],
        );
    }

    private static function normalize(array $input): array
    {
        $number = fn ($v) => is_string($v) ? str_replace([' ', "\u{00A0}", ','], ['', '', '.'], trim($v)) : $v;

        foreach ($input as $key => $value) {
            $input[$key] = $key === 'presets'
                ? array_values(array_filter(array_map($number, (array) $value), fn ($v) => $v !== ''))
                : $number($value);
        }

        return $input;
    }

    private static function cast(array $s, bool $onlyPresent = false): array
    {
        foreach (['min_premium', 'min', 'max', 'default', 'step', 'term_months', 'start_offset'] as $k) {
            if (array_key_exists($k, $s)) {
                $s[$k] = (int) $s[$k];
            }
        }
        if (array_key_exists('rate', $s)) {
            $s['rate'] = (float) $s['rate'];
        }
        if (array_key_exists('max_start_days', $s)) {
            $s['max_start_days'] = $s['max_start_days'] === null ? null : (int) $s['max_start_days'];
        }
        if (array_key_exists('presets', $s)) {
            $s['presets'] = array_values(array_map('intval', (array) $s['presets']));
            if ($onlyPresent) {
                $s['presets'] = array_values(array_unique($s['presets']));
                sort($s['presets']);
            }
        }

        return $s;
    }

    private static function isSane(array $f): bool
    {
        return $f['min'] > 0 && $f['max'] > $f['min'] && $f['step'] > 0
            && $f['term_months'] > 0 && (!isset($f['rate']) || $f['rate'] > 0);
    }

    /** Field => message for combinations that each look fine alone */
    private static function crossCheck(array $f): array
    {
        $errors = [];

        if ($f['max'] <= $f['min']) {
            $errors['max'] = 'Maksimal summa minimal summadan katta bo\'lishi kerak.';
        }
        if ($f['default'] < $f['min'] || $f['default'] > $f['max']) {
            $errors['default'] = 'Standart summa minimal va maksimal oralig\'ida bo\'lishi kerak.';
        }
        foreach ($f['presets'] as $p) {
            if ($p < $f['min'] || $p > $f['max']) {
                $errors['presets'] = self::short($p) . ' minimal va maksimal summa oralig\'idan tashqarida.';
                break;
            }
        }

        // The browser snaps a range input to min + k·step, so every offered sum must sit on that grid
        foreach (array_merge([$f['default']], $f['presets']) as $sum) {
            if ($f['step'] > 0 && ($sum - $f['min']) % $f['step'] !== 0) {
                $errors['step'] ??= 'Qadam ' . self::short($f['step']) . ' bo\'lsa, slayder ' . self::short($sum)
                    . ' summasiga tushmaydi. Qadam (summa − minimal) farqini qoldiqsiz bo\'lishi kerak.';
            }
        }

        if ($f['max_start_days'] !== null && $f['max_start_days'] < $f['start_offset']) {
            $errors['max_start_days'] = 'Eng kech boshlanish eng erta boshlanishdan oldin bo\'lmasligi kerak.';
        }

        return $errors;
    }

    /** Largest step ≤ the configured one that still lands on the default and every preset */
    private static function safeStep(array $f): int
    {
        $step = $f['step'];
        foreach (array_merge([$f['default']], $f['presets']) as $sum) {
            $step = self::gcd($step, $sum - $f['min']);
        }

        return max($step, 1);
    }

    private static function gcd(int $a, int $b): int
    {
        [$a, $b] = [abs($a), abs($b)];
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }

        return $a;
    }

    /** 5000000 → "5 mln", 750000 → "750 000 so'm" */
    private static function short(int $sum): string
    {
        return $sum >= 1_000_000 && $sum % 100_000 === 0
            ? str_replace('.', ',', (string) ($sum / 1_000_000)) . ' mln'
            : formatMoney($sum);
    }
}
