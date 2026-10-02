<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Insured-event reports (/{locale}/claims): the customer describes what happened and attaches
 * photos / documents; staff handle it in the admin panel; the status is checked by number + phone.
 */
final class ClaimController extends Controller
{
    public const MAX_FILES = 5;
    public const MAX_FILE_KB = 10240;

    /** Claim numbers this browser just filed (shown on the "sent" page) */
    private const SESSION_FILED = 'claims.filed';

    public function __construct(private readonly OrderService $orderService) {}

    // ─── Report ───────────────────────────────────────────────────────────────

    /** ?order=ID pre-fills the policy of an order this visitor may see ("Mening polislarim") */
    public function create(Request $request): View
    {
        $order = $request->integer('order') ? Order::find($request->integer('order')) : null;
        $order = $order && $this->orderService->canSeeDetails($order) ? $order : null;

        $response = $order?->insurances_response_data ?? [];

        return view('pages.claims.create', [
            'products' => $this->products(),
            'prefill'  => [
                'product'       => $order?->insurances_data['_product_key'] ?? (is_string($request->query('product')) ? $request->query('product') : null),
                'policy_number' => trim(($response['polis_sery'] ?? '') . ' ' . ($response['polis_number'] ?? '')) ?: $order?->insurance_id,
                'full_name'     => $order?->client_name,
                'phone'         => $order?->phone ?? $this->orderService->verifiedPhone(),
                'order_id'      => $order?->id,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Bots fill every field, people never see this one
        if (filled($request->input('website'))) {
            return redirect()->route('claims.create', ['locale' => getCurrentLocale()]);
        }

        $request->merge(['phone' => $this->cleanPhone($request->input('phone'))]);

        $data = $request->validate([
            'product'       => ['nullable', 'string', 'max:30'],
            'policy_number' => ['required', 'string', 'max:40'],
            'full_name'     => ['required', 'string', 'max:150'],
            'phone'         => ['required', 'regex:/^998[0-9]{9}$/'],
            'event_date'    => ['required', 'date', 'before_or_equal:today', 'after:' . now()->subYears(3)->format('Y-m-d')],
            'description'   => ['required', 'string', 'min:20', 'max:3000'],
            'files'         => ['nullable', 'array', 'max:' . self::MAX_FILES],
            'files.*'       => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:' . self::MAX_FILE_KB],
            'order_id'      => ['nullable', 'integer'],
        ], [
            'phone.regex' => __t('messages.flow.phone_help'),
        ]);

        // Only link an order this visitor may see
        $order = !empty($data['order_id']) ? Order::find($data['order_id']) : null;
        $order = $order && $this->orderService->canSeeDetails($order) ? $order : null;

        $claim = Claim::create([
            'number'        => Claim::newNumber(),
            'order_id'      => $order?->id,
            'product'       => in_array($data['product'] ?? null, $this->products()->keys()->all(), true) ? $data['product'] : null,
            'policy_number' => strtoupper(trim($data['policy_number'])),
            'full_name'     => trim($data['full_name']),
            'phone'         => $data['phone'],
            'event_date'    => $data['event_date'],
            'description'   => trim($data['description']),
            'status'        => Claim::STATUS_NEW,
            'locale'        => getCurrentLocale(),
        ]);

        $files = [];
        foreach ($request->file('files', []) as $file) {
            $files[] = [
                'path' => $file->store('claims/' . $claim->id, 'local'),
                'name' => mb_substr($file->getClientOriginalName(), 0, 120),
                'size' => $file->getSize(),
            ];
        }
        if ($files) {
            $claim->update(['files' => $files]);
        }

        Log::info('Claim filed', ['number' => $claim->number, 'files' => count($files)]);

        session()->push(self::SESSION_FILED, $claim->number);

        return redirect()->route('claims.sent', ['locale' => getCurrentLocale(), 'number' => $claim->number]);
    }

    /** "Your claim number is …" — only for the browser that filed it */
    public function sent(string $locale, string $number): View|RedirectResponse
    {
        if (!in_array($number, (array) session(self::SESSION_FILED, []), true)) {
            return redirect()->route('claims.status', ['locale' => $locale]);
        }

        return view('pages.claims.sent', ['number' => $number]);
    }

    // ─── Status ───────────────────────────────────────────────────────────────

    public function status(Request $request): View
    {
        $claim = null;

        if ($request->filled('number')) {
            $request->merge(['phone' => $this->cleanPhone($request->input('phone'))]);
            $request->validate([
                'number' => ['required', 'string', 'max:20'],
                'phone'  => ['required', 'regex:/^998[0-9]{9}$/'],
            ], ['phone.regex' => __t('messages.flow.phone_help')]);

            // Number and phone must both match: numbers alone could be tried one by one
            $claim = Claim::where('number', strtoupper(trim($request->input('number'))))
                ->where('phone', $request->input('phone'))
                ->first();
        }

        return view('pages.claims.status', [
            'claim'    => $claim,
            'searched' => $request->filled('number'),
            'products' => $this->products(),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** route => name in the current locale, products on sale */
    private function products()
    {
        $locale = getCurrentLocale();

        return Product::where('is_active', true)->orderBy('sort_order')->get()
            ->mapWithKeys(fn (Product $p) => [trim((string) $p->route, '/') => $p->{'name_' . $locale} ?: $p->name_uz]);
    }

    private function cleanPhone(?string $phone): string
    {
        $phone = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($phone, '00998')) {
            $phone = substr($phone, 2);
        }

        return strlen($phone) === 9 ? '998' . $phone : $phone;
    }
}
