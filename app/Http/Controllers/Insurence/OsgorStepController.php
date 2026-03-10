<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use App\Traits\Api;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OsgorStepController extends Controller
{
    use Api;

    private const SESSION_KEY = 'osgor';

    private function getRegions(): array
    {
        $response = $this->sendRequest('/api/references/regions', [], 'GET');
        $data = json_decode($response->body(), true);
        return $data['result'] ?? [];
    }

    private function guardStep(int $step): ?RedirectResponse
    {
        for ($i = 1; $i < $step; $i++) {
            if (!session()->has(self::SESSION_KEY . '.step' . $i)) {
                return redirect()->route('osgor.step' . $i, ['locale' => getCurrentLocale()]);
            }
        }
        return null;
    }

    private function cleanPhone(?string $phone): string
    {
        if (!$phone) return '';
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '998')) return substr($digits, 0, 12);
        if (strlen($digits) === 9) return '998' . $digits;
        if (strlen($digits) > 9) return '998' . substr($digits, -9);
        return $digits;
    }

    // ──────────────────────────────────────────────────
    // STEP 1 — Contract Info
    // ──────────────────────────────────────────────────

    public function step1(): View
    {
        $data = session(self::SESSION_KEY . '.step1', []);
        return view('pages.insurence.osgor.step1', compact('data'));
    }

    public function step1Store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $endDate = Carbon::parse($validated['payment_start_date'])
            ->addYear()
            ->subDay()
            ->format('Y-m-d');

        session([self::SESSION_KEY . '.step1' => array_merge($validated, [
            'payment_end_date' => $endDate,
        ])]);

        return redirect()->route('osgor.step2', ['locale' => getCurrentLocale()]);
    }

    // ──────────────────────────────────────────────────
    // STEP 2 — Organization Info
    // ──────────────────────────────────────────────────

    public function step2(): View|RedirectResponse
    {
        if ($guard = $this->guardStep(2)) return $guard;
        $regions = $this->getRegions();
        $data    = session(self::SESSION_KEY . '.step2', []);
        return view('pages.insurence.osgor.step2', compact('regions', 'data'));
    }

    public function step2Store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization.inn'       => ['required', 'string', 'size:9', 'regex:/^\d{9}$/'],
            'organization.name'      => ['required', 'string', 'max:255'],
            'organization.address'   => ['required', 'string', 'max:500'],
            'organization.oked'      => ['required', 'string'],
            'organization.phone'     => ['required', 'string'],
            'organization.region_id' => ['required', 'string'],
            'organization.district_id' => ['required', 'string'],
        ], [
            'organization.inn.size'     => __('messages.inn_invalid'),
            'organization.inn.regex'    => __('messages.inn_invalid'),
            'organization.region_id.required' => __('validation.required', ['attribute' => __('messages.region')]),
            'organization.district_id.required' => __('validation.required', ['attribute' => __('messages.district')]),
        ]);

        if (isset($validated['organization']['phone'])) {
            $validated['organization']['phone'] = $this->cleanPhone($validated['organization']['phone']);
        }
        $validated['organization']['ownership_form_id'] = $request->input('organization.ownership_form_id', '130');

        session([self::SESSION_KEY . '.step2' => $validated]);
        return redirect()->route('osgor.step3', ['locale' => getCurrentLocale()]);
    }

    // ──────────────────────────────────────────────────
    // STEP 3 — Policy Info
    // ──────────────────────────────────────────────────

    public function step3(): View|RedirectResponse
    {
        if ($guard = $this->guardStep(3)) return $guard;
        $data = session(self::SESSION_KEY . '.step3', []);
        $insuranceAmount = ['min' => 50000000, 'max' => 500000000];
        return view('pages.insurence.osgor.step3', compact('data', 'insuranceAmount'));
    }

    public function step3Store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'insurance_amount' => ['required', 'numeric', 'min:50000000', 'max:500000000'],
        ], [
            'insurance_amount.min' => __('validation.min.numeric', ['attribute' => __('messages.insurance_amount'), 'min' => '50,000,000']),
            'insurance_amount.max' => __('validation.max.numeric', ['attribute' => __('messages.insurance_amount'), 'max' => '500,000,000']),
        ]);

        session([self::SESSION_KEY . '.step3' => $validated]);
        return redirect()->route('osgor.step4', ['locale' => getCurrentLocale()]);
    }

    // ──────────────────────────────────────────────────
    // STEP 4 — Review & Submit
    // ──────────────────────────────────────────────────

    public function step4(): View|RedirectResponse
    {
        if ($guard = $this->guardStep(4)) return $guard;
        $step1 = session(self::SESSION_KEY . '.step1');
        $step2 = session(self::SESSION_KEY . '.step2');
        $step3 = session(self::SESSION_KEY . '.step3');
        return view('pages.insurence.osgor.step4', compact('step1', 'step2', 'step3'));
    }
}
