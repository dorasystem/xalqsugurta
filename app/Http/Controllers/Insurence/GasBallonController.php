<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\GazApplicationRequest;
use App\Services\GazBallonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

final class GasBallonController extends Controller
{
    public function __construct(private readonly GazBallonService $service) {}

    public function main(): View
    {
        return view('pages.insurence.gas.main');
    }

    public function applicationView(): View
    {
        return view('pages.insurence.gas.main');
    }

    public function application(GazApplicationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Compute premium: sum_bank * 0.5%
        $sumBank = (int) data_get($data, 'cost.sum_bank', 0);
        $premium = (int) round($sumBank * 0.005);

        // Prepare final payload for initiate (store it for the storage step or call immediately)
        $payload = $this->mapToInitiatePayload($data, $premium);

        // Call API
        $result = $this->service->initiateTransaction($payload);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message'] ?? __('messages.request_error')])->withInput();
        }

        $response = $result['data'] ?? [];

        // Handle existing transaction result = 302
        if ((int) data_get($response, 'result') === 302) {
            return back()->with([
                'existing_policy' => [
                    'polis_sery' => data_get($response, 'polis_sery'),
                    'polis_number' => data_get($response, 'polis_number'),
                    'polis_check' => data_get($response, 'polis_check'),
                ],
            ])->withInput();
        }

        // Success flow identical to Accident (redirect to payment or confirmation as needed)
        return back()->with('success', __('success.insurance.order_created'));
    }

    private function mapToInitiatePayload(array $data, int $premium): array
    {
        $applicant = (array) data_get($data, 'applicant', []);
        $property = (array) data_get($data, 'property', []);
        $details = (array) data_get($data, 'details', []);
        $sumBank = (int) data_get($data, 'cost.sum_bank', 0);

        $fullName = trim(implode(' ', [
            (string) data_get($applicant, 'lastName'),
            (string) data_get($applicant, 'firstName'),
            (string) data_get($applicant, 'middleName'),
        ]));

        $birthDate = data_get($applicant, 'birthDate');
        $sDate = data_get($details, 'startDate');
        $eDate = data_get($details, 'endDate');

        return [
            'subject' => 'P',
            'customer' => [
                'full_name' => $fullName,
                'birth_date' => $birthDate ? Carbon::parse($birthDate)->format('d.m.Y') : '',
                'gender' => (int) data_get($applicant, 'gender', 1),
                'passport' => (string) data_get($applicant, 'passportSeries') . (string) data_get($applicant, 'passportNumber'),
                'pinfl' => (string) data_get($applicant, 'pinfl'),
                'phone' => (string) data_get($applicant, 'phoneNumber', ''),
                'address' => (string) data_get($applicant, 'address', ''),
                'oked' => (string) data_get($applicant, 'oked', ''),
                'ras_sum' => (string) data_get($applicant, 'ras_sum', ''),
                'subj_mfo' => (string) data_get($applicant, 'subj_mfo', ''),
                'bxm' => (string) data_get($applicant, 'bxm', ''),
                'representativename' => (string) data_get($applicant, 'representativename', ''),
            ],
            'loan_info' => [
                'loan_type' => '35',
                'claim_id' => (string) Str::ulid(),
                'contract_number' => (string) Str::uuid(),
                'contract_date' => now()->format('d.m.Y'),
                's_date' => $sDate ? Carbon::parse($sDate)->format('d.m.Y') : '',
                'e_date' => $eDate ? Carbon::parse($eDate)->format('d.m.Y') : '',
                'cadastr_info' => [
                    'cadastr_number' => (string) data_get($property, 'cadasterNumber'),
                    'address' => (string) data_get($property, 'shortAddress', ''),
                    'regionid' => (string) data_get($property, 'region', ''),
                    'districtid' => (string) data_get($property, 'districtId', ''),
                    'sum_bank' => $sumBank,
                    'is_owner' => (int) ((bool) data_get($property, 'is_owner', true)),
                ],
            ],
        ];
    }
}
