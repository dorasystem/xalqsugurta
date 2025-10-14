<?php

namespace App\Http\Controllers\Insurence;

use App\Actions\Insurence\ProcessAccidentApplicationAction;
use App\DTOs\AccidentApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\AccidentApplicationRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class AccidentController extends Controller
{
    use HandlesInsuranceErrors;
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function main(): View
    {
        return view('pages.insurence.accident.main');
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

    public function application(
        AccidentApplicationRequest $request,
        ProcessAccidentApplicationAction $action
    ): View|RedirectResponse {
        try {
            // Create DTO from validated request data
            $applicationData = AccidentApplicationData::fromRequest($request->validated());
            // Execute the action to send data to API
            // $result = $action->execute($applicationData);

            // if (!$result['success']) {
            //     return back()
            //         ->withErrors(['error' => $result['error']])
            //         ->withInput();
            // }

            // Store data in session for GET requests (language switching and order creation)
            session([
                'accident_application_data' => $applicationData->toArray(),
                'accident_api_response' => $result['data'] ?? null,
            ]);

            // Pass the structured data to the view
            return view('pages.insurence.accident.application', [
                'applicationData' => $applicationData->toArray(),
                'apiResponse' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            return $this->handleGeneralError('accident', $e, 'application');
        }
    }

    public function storage(): RedirectResponse
    {
        try {
            if (!session()->has('accident_application_data')) {
                return $this->handleSessionNotFound('accident');
            }

            $applicationData = session('accident_application_data');
            $apiResponse = session('accident_api_response');

            Log::info('Accident storage: Creating order', [
                'has_application_data' => !empty($applicationData),
                'has_api_response' => !empty($apiResponse),
            ]);

            // Create order
            $orderData = [
                'product_name' => 'OSAGO Sug\'urta',
                'amount' => $applicationData['insurancePremium'] ?? 0,
                'state' => 0,
                'insurance_id' => $apiResponse['id'] ?? uniqid('acc_'),
                'phone' => $applicationData['client']['phoneNumber'] ?? null,
                'insurances_data' => $applicationData,
                'insurances_response_data' => $apiResponse,
                'status' => Order::STATUS_NEW,
            ];

            $order = $this->orderService->createOrder($orderData);

            Log::info('Accident storage: Order created successfully', ['order_id' => $order->id]);

            return $this->redirectWithSuccess(
                'payment.show',
                ['locale' => getCurrentLocale(), 'orderId' => $order->id],
                __('success.insurance.order_created')
            );
        } catch (\Exception $e) {
            return $this->handleOrderCreationError('accident', $e);
        }
    }

    public function calculation(): View
    {
        return view('pages.insurence.accident.calculation');
    }
}
