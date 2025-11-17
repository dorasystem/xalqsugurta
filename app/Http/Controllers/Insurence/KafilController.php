<?php

namespace App\Http\Controllers\Insurence;

use App\DTOs\AccidentApplicationData;
use App\DTOs\KafilApplicationData; // <-- DTO o'zgardi
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\AccidentApplicationRequest;
use App\Services\AccidentStorageService;
use App\Services\InsuranceApiConfiguratorService;
use App\Services\InsuranceApiService;
use App\Services\KafilStorageService;
use App\Services\OrderService;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class KafilController extends Controller
{
    use HandlesInsuranceErrors;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly InsuranceApiService $apiService,
        private readonly InsuranceApiConfiguratorService $apiConfigurator,
        private readonly KafilStorageService $storageService
    ) {
        $this->apiConfigurator->setupAccidentApi($this->apiService);
    }

    public function main(): View
    {
        return view('pages.insurence.kafil.main');
    }

    public function application(AccidentApplicationRequest $request): View|RedirectResponse
    {
        try {
            $applicationData = KafilApplicationData::fromRequest($request->validated());

            $requestData = $applicationData->toApiFormat();
            $result = $this->apiService->sendApplication($requestData);

            if (!$result['success']) {
                return back()->withErrors(['error' => $result['error']])->withInput();
            }

            session([
                'kafil_application_data' => $applicationData->toArray(),
                'kafil_api_response' => $result['data'] ?? null,
            ]);

            return view('pages.insurence.kafil.application', [
                'applicationData' => $applicationData->toArray(),
                'apiResponse' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            return $this->handleGeneralError('kafil', $e, 'application');
        }
    }

    public function applicationView(): View|RedirectResponse
    {
        if (!session()->has('accident_application_data')) {
            return $this->handleSessionNotFound('accident');
        }

        $applicationData = session('accident_application_data');
        return view('pages.insurence.kafil.application', [
            'applicationData' => $applicationData,
        ]);
    }

    public function storage(): RedirectResponse
    {
        return $this->storageService->handle();
    }

    public function calculation(): View
    {
        return view('pages.insurence.kafil.calculation');
    }
}
