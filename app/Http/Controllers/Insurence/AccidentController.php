<?php

namespace App\Http\Controllers\Insurence;

use App\DTOs\AccidentApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\AccidentApplicationRequest;
use App\Services\AccidentStorageService;
use App\Services\InsuranceApiConfiguratorService;
use App\Services\InsuranceApiService;
use App\Services\OrderService;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class AccidentController extends Controller
{
    use HandlesInsuranceErrors;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly InsuranceApiService $apiService,
        private readonly InsuranceApiConfiguratorService $apiConfigurator,
        private readonly AccidentStorageService $storageService
    ) {
        $this->apiConfigurator->setupAccidentApi($this->apiService);
    }

    public function main(): View
    {
        return view('pages.insurence.accident.main');
    }

    public function application(AccidentApplicationRequest $request): View|RedirectResponse
    {
        try {
            $applicationData = AccidentApplicationData::fromRequest($request->validated());

            $requestData = $applicationData->toApiFormat();
            $result = $this->apiService->sendApplication($requestData);

            if (!$result['success']) {
                return back()
                    ->withErrors(['error' => $result['error']])
                    ->withInput();
            }

            session([
                'accident_application_data' => $applicationData->toArray(),
                'accident_api_response' => $result['data'] ?? null,
            ]);

            return view('pages.insurence.accident.application', [
                'applicationData' => $applicationData->toArray(),
                'apiResponse' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            return $this->handleGeneralError('accident', $e, 'application');
        }
    }

    public function applicationView(): View|RedirectResponse
    {
        if (!session()->has('accident_application_data')) {
            return $this->handleSessionNotFound('accident');
        }

        $applicationData = session('accident_application_data');
        return view('pages.insurence.accident.application', [
            'applicationData' => $applicationData,
        ]);
    }

    public function storage(): RedirectResponse
    {
        return $this->storageService->handle();
    }

    public function calculation(): View
    {
        return view('pages.insurence.accident.calculation');
    }
}
