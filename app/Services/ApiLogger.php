<?php

namespace App\Services;

use App\Models\ApiLog;
use App\Models\Order;
use App\Models\Product;
use Closure;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Throwable;

/**
 * Writes every HTTP request to the insurer's API (online.xalqsugurta.uz) to api_logs,
 * so the admin panel can show what was sent and what came back. Hooks the Laravel
 * HTTP client events, so each retry attempt is its own row. The Authorization header
 * is never stored.
 */
final class ApiLogger
{
    /** Longest response body kept, in characters */
    private const MAX_RESPONSE = 20_000;

    private static ?int $orderId = null;

    private static ?string $product = null;

    /** Rows written during this request that have no order yet (linked by attachToOrder) */
    private static array $pending = [];

    public static function register(): void
    {
        Event::listen(ResponseReceived::class, fn (ResponseReceived $e) => self::record($e->request, $e->response));
        Event::listen(ConnectionFailed::class, fn (ConnectionFailed $e) => self::record($e->request, null, $e->exception->getMessage()));
    }

    /** Runs $callback with the rows it writes tied to the order */
    public static function forOrder(Order $order, ?string $product, Closure $callback): mixed
    {
        [$previousOrder, $previousProduct] = [self::$orderId, self::$product];
        [self::$orderId, self::$product] = [$order->id, $product];

        try {
            return $callback();
        } finally {
            [self::$orderId, self::$product] = [$previousOrder, $previousProduct];
        }
    }

    /** Ties the rows written earlier in this request (the contract submit) to the new order */
    public static function attachToOrder(Order $order): void
    {
        if (!self::$pending) {
            return;
        }

        try {
            ApiLog::whereIn('id', self::$pending)->whereNull('order_id')->update(['order_id' => $order->id]);
        } catch (Throwable) {
            // Logging must never break the sale
        }

        self::$pending = [];
    }

    public static function record(Request $request, ?Response $response, ?string $error = null): void
    {
        if (!self::isInsurerUrl($request->url())) {
            return;
        }

        try {
            $json   = $response?->json();
            $json   = is_array($json) ? $json : [];   // HTML error pages decode to null
            // Calculators and sales answer with a scalar "result"; the proxy with "error" (its "result" is the data)
            $result = collect([$json['result'] ?? null, $json['error'] ?? null])
                ->first(fn ($v) => is_scalar($v) && $v !== '');
            $result = $result === null ? null : (string) $result;
            $stats  = $response?->transferStats;

            $log = ApiLog::create([
                'order_id'    => self::$orderId,
                'product'     => self::$product ?? self::productFromRoute(),
                'method'      => $request->method(),
                'endpoint'    => self::endpoint($request),
                'url'         => Str::limit($request->url(), 490, ''),
                'status'      => $response?->status(),
                'result'      => $result,
                'success'     => $response !== null && $response->successful() && in_array($result, [null, '0', '302'], true),
                'duration_ms' => $stats ? (int) round($stats->getTransferTime() * 1000) : null,
                'request'     => $request->data() ?: null,
                'request_raw' => $request->body() !== '' ? Str::limit($request->body(), self::MAX_RESPONSE) : null,
                'response'    => $response ? Str::limit($response->body(), self::MAX_RESPONSE) : null,
                'error'       => $error ? Str::limit($error, 490) : null,
            ]);

            if (self::$orderId === null) {
                self::$pending[] = $log->id;
            }
        } catch (Throwable) {
            // Logging must never break a sale or a payment callback (and must not write to the app log)
        }
    }

    /** Hosts of every insurer URL in the config */
    private static function isInsurerUrl(string $url): bool
    {
        static $hosts = null;

        $hosts ??= collect([
            config('provider.base_url'),
            config('provider.xalq.base_url'),
            config('services.insurance.osago.endpoint'),
            ...array_values((array) config('provider.calc')),
            ...array_values((array) config('provider.submit')),
        ])->filter()->map(fn ($u) => parse_url((string) $u, PHP_URL_HOST))->filter()->push('online.xalqsugurta.uz')->unique()->all();

        return in_array(parse_url($url, PHP_URL_HOST), $hosts, true);
    }

    /** "website/accident/sale", "osago/proxy · pinfl-v2" */
    private static function endpoint(Request $request): string
    {
        $path = (string) parse_url($request->url(), PHP_URL_PATH);
        $path = Str::after($path, '/xs/ins/');
        $path = str_replace('unv/gazballonsayt/', 'unv/', $path);

        $param = $request->header('param')[0] ?? null;
        if ($param) {
            $path .= ' · ' . basename($param);
        }

        return Str::limit(trim($path, '/'), 145, '');
    }

    /** Product key from the current route name ("gas.storeApplication" → "gas") */
    private static function productFromRoute(): ?string
    {
        $name = request()->route()?->getName();
        $key  = $name ? Str::before($name, '.') : null;

        return $key && isset(Product::CATEGORIES[$key]) ? $key : null;
    }
}
