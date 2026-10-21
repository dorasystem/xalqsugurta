<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\PhoneVerification;
use App\Services\SmsSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * "Mening polislarim": the visitor proves a phone number with an SMS code and then sees
 * every order placed with that number (policy links, payment of unpaid ones).
 */
final class MyPoliciesController extends Controller
{
    /** Phone waiting for its code (between "send" and "verify") */
    private const SESSION_PENDING = 'my_policies.pending';

    public function __construct(
        private readonly PhoneVerification $verification,
        private readonly OrderService $orderService,
    ) {}

    // ─── Pages ────────────────────────────────────────────────────────────────

    public function index(): View
    {
        $phone = $this->orderService->verifiedPhone();

        if ($phone === null) {
            return view('pages.my-policies.login', [
                'pending'   => session(self::SESSION_PENDING),
                'available' => SmsSettings::ready(),
            ]);
        }

        return view('pages.my-policies.list', [
            'phone'  => $phone,
            'orders' => Order::where('phone', $phone)->latest()->limit(100)->get(),
        ]);
    }

    // ─── Step 1: phone → SMS code ─────────────────────────────────────────────

    public function sendCode(Request $request): RedirectResponse
    {
        $request->merge(['phone' => $this->cleanPhone($request->input('phone'))]);
        $request->validate(['phone' => ['required', 'regex:/^998[0-9]{9}$/']], [
            'phone.required' => __t('messages.flow.phone_help'),
            'phone.regex'    => __t('messages.flow.phone_help'),
        ]);

        $phone  = $request->input('phone');
        $result = $this->verification->send($phone, (string) $request->ip());

        if ($result !== PhoneVerification::SENT) {
            return back()->withErrors(['phone' => __t('messages.my_policies.error_' . $result)])->withInput();
        }

        session([self::SESSION_PENDING => $phone]);

        return redirect()->route('my-policies', ['locale' => getCurrentLocale()])
            ->with('status', __t('messages.my_policies.code_sent', ['phone' => formatPhone($phone)]));
    }

    // ─── Step 2: code → signed in ─────────────────────────────────────────────

    public function verify(Request $request): RedirectResponse
    {
        $phone = session(self::SESSION_PENDING);
        if (!$phone) {
            return redirect()->route('my-policies', ['locale' => getCurrentLocale()]);
        }

        $request->validate(['code' => ['required', 'digits:6']], [
            'code.required' => __t('messages.my_policies.code_wrong'),
            'code.digits'   => __t('messages.my_policies.code_wrong'),
        ]);

        if (!$this->verification->verify($phone, $request->input('code'))) {
            return back()->withErrors(['code' => __t('messages.my_policies.code_wrong')]);
        }

        // New session id: the signed-in state must not ride on an id issued before login
        $request->session()->regenerate();
        session()->forget(self::SESSION_PENDING);
        session([OrderService::SESSION_PHONE => [
            'phone' => $phone,
            'until' => now()->addMinutes(OrderService::PHONE_SESSION_MINUTES)->timestamp,
        ]]);

        Log::info('My policies: phone verified');

        return redirect()->route('my-policies', ['locale' => getCurrentLocale()]);
    }

    /** Back to the phone form (another number, or signing out) */
    public function logout(): RedirectResponse
    {
        session()->forget([OrderService::SESSION_PHONE, self::SESSION_PENDING]);

        return redirect()->route('my-policies', ['locale' => getCurrentLocale()]);
    }

    /** Digits only, 998XXXXXXXXX when it can be */
    private function cleanPhone(?string $phone): string
    {
        $phone = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($phone, '00998')) {
            $phone = substr($phone, 2);
        }

        return strlen($phone) === 9 ? '998' . $phone : $phone;
    }
}
