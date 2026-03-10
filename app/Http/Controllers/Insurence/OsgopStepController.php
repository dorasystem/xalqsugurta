<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\Osgop\Step1Request;
use App\Http\Requests\Insurence\Osgop\Step2Request;
use App\Http\Requests\Insurence\Osgop\Step3Request;
use App\Http\Requests\Insurence\Osgop\Step4Request;
use App\Http\Requests\Insurence\Osgop\Step5Request;
use App\Traits\Api;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OsgopStepController extends Controller
{
    use Api;

    private const SESSION_KEY = 'osgop';
    private const TERM_MAP    = ['3' => 3, '6' => 6, '12' => 7];

    private function getRegions(): array
    {
        try {
            $response = $this->sendRequest('/api/references/regions', [], 'GET');
            return json_decode($response->body(), true)['result'] ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Redirect to step N if any previous step is missing from session.
     */
    private function guardStep(int $step): ?RedirectResponse
    {
        for ($i = 1; $i < $step; $i++) {
            if (!session()->has(self::SESSION_KEY . '.step' . $i)) {
                return redirect()->route('osgop.create.step' . $i, ['locale' => getCurrentLocale()]);
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

    // ── STEP 1: Contract Info ──────────────────────────────────────────────

    public function step1(): View
    {
        $data = session(self::SESSION_KEY . '.step1', []);
        return view('pages.insurence.osgop.step1', compact('data'));
    }

    public function step1Post(Step1Request $request): RedirectResponse
    {
        $v     = $request->validated();
        $start = $v['start_date'];
        $end   = Carbon::parse($start)->addMonths((int) $v['period_months'])->subDay()->format('Y-m-d');

        session([self::SESSION_KEY . '.step1' => [
            'start_date'          => $start,
            'period_months'       => $v['period_months'],
            'end_date'            => $end,
            'contract_start_date' => $start,
            'contract_end_date'   => $end,
            'area_type_id'        => '1',
            'agency_id'           => '546',
            'comission'           => '0',
            'number'              => '',
            'insurance_term_id'   => self::TERM_MAP[$v['period_months']] ?? 7,
        ]]);

        return redirect()->route('osgop.create.step2', ['locale' => getCurrentLocale()]);
    }

    // ── STEP 2: Insurant Type Selection ───────────────────────────────────

    public function step2(): View|RedirectResponse
    {
        if ($guard = $this->guardStep(2)) return $guard;
        $data = session(self::SESSION_KEY . '.step2', []);
        return view('pages.insurence.osgop.step2', compact('data'));
    }

    public function step2Post(Step2Request $request): RedirectResponse
    {
        session([self::SESSION_KEY . '.step2' => $request->validated()]);
        return redirect()->route('osgop.create.step3', ['locale' => getCurrentLocale()]);
    }

    // ── STEP 3: Insurant Form ─────────────────────────────────────────────

    public function step3(): View|RedirectResponse
    {
        if ($guard = $this->guardStep(3)) return $guard;
        $regions      = $this->getRegions();
        $insurantType = session(self::SESSION_KEY . '.step2.insurant_type', 'organization');
        $data         = session(self::SESSION_KEY . '.step3', []);
        return view('pages.insurence.osgop.step3', compact('regions', 'data', 'insurantType'));
    }

    public function step3Post(Step3Request $request): RedirectResponse
    {
        $type      = session(self::SESSION_KEY . '.step2.insurant_type', 'organization');
        $validated = $request->validated();

        if ($type === 'organization') {
            $org = $validated['organization'] ?? [];
            $allowed = ['inn', 'name', 'representative_name', 'address', 'oked', 'position', 'phone', 'region_id', 'ownership_form_id'];
            $validated['organization'] = array_intersect_key($org, array_flip($allowed));
            $validated['organization']['phone']             = $this->cleanPhone($validated['organization']['phone'] ?? null);
            $validated['organization']['ownership_form_id'] = $validated['organization']['ownership_form_id'] ?? '130';
        } else {
            $validated['person']['phone']         = $this->cleanPhone($validated['person']['phone'] ?? null);
            $validated['person']['resident_type'] = $request->input('person.resident_type', '1');
            $validated['person']['country_id']    = $request->input('person.country_id', '210');
        }

        session([self::SESSION_KEY . '.step3' => $validated]);
        return redirect()->route('osgop.create.step4', ['locale' => getCurrentLocale()]);
    }

    // ── STEP 4: Vehicle Info ──────────────────────────────────────────────

    public function step4(): View|RedirectResponse
    {
        if ($guard = $this->guardStep(4)) return $guard;
        $regions = $this->getRegions();
        $data    = session(self::SESSION_KEY . '.step4', []);
        return view('pages.insurence.osgop.step4', compact('regions', 'data'));
    }

    public function step4Post(Step4Request $request): RedirectResponse
    {
        session([self::SESSION_KEY . '.step4' => $request->validated()]);
        return redirect()->route('osgop.create.step5', ['locale' => getCurrentLocale()]);
    }

    // ── STEP 5: Policy Info ───────────────────────────────────────────────

    public function step5(): View|RedirectResponse
    {
        if ($guard = $this->guardStep(5)) return $guard;
        $step1 = session(self::SESSION_KEY . '.step1');
        $data  = session(self::SESSION_KEY . '.step5', []);
        return view('pages.insurence.osgop.step5', compact('step1', 'data'));
    }

    public function step5Post(Step5Request $request): RedirectResponse
    {
        $v     = $request->validated();
        $step1 = session(self::SESSION_KEY . '.step1', []);

        session([self::SESSION_KEY . '.step5' => array_merge($v, [
            'insurance_term_id' => $step1['insurance_term_id'] ?? 7,
        ])]);

        return redirect()->route('osgop.create.step6', ['locale' => getCurrentLocale()]);
    }

    // ── STEP 6: Review & Submit ───────────────────────────────────────────

    public function step6(): View|RedirectResponse
    {
        if ($guard = $this->guardStep(6)) return $guard;
        $step1 = session(self::SESSION_KEY . '.step1');
        $step2 = session(self::SESSION_KEY . '.step2');
        $step3 = session(self::SESSION_KEY . '.step3');
        $step4 = session(self::SESSION_KEY . '.step4');
        $step5 = session(self::SESSION_KEY . '.step5');
        return view('pages.insurence.osgop.step6', compact('step1', 'step2', 'step3', 'step4', 'step5'));
    }
}
