<?php

namespace App\Http\Controllers\Insurence;

use App\Actions\Insurence\ProcessPropertyApplicationAction;
use App\DTOs\PropertyApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\PropertyApplicationRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PropertyService;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class PropertyController extends Controller
{
    use HandlesInsuranceErrors;
    public function __construct(
        private readonly PropertyService $propertyService,
        private readonly OrderService $orderService
    ) {}

    public function main(): View
    {
        return view('pages.insurence.property.main');
    }

    public function applicationView(): View|RedirectResponse
    {
        if (!session()->has('property_application_data')) {
            return $this->handleSessionNotFound('property');
        }

        $applicationData = session('property_application_data');
        return view('pages.insurence.property.application', [
            'applicationData' => $applicationData,
        ]);
    }

    public function application(
        PropertyApplicationRequest $request,
        ProcessPropertyApplicationAction $action
    ): View|RedirectResponse {
        try {
            // Create DTO from validated request data
            $applicationData = PropertyApplicationData::fromRequest($request->validated());



            // Execute the action to send data to API
            $result = $action->execute($applicationData);

            if (!$result['success']) {
                return back()
                    ->withErrors(['error' => $result['error']])
                    ->withInput();
            }

            // Store data in session for GET requests (language switching and order creation)
            session([
                'property_application_data' => $applicationData->toArray(),
                'property_api_response' => $result['data'] ?? null,
            ]);

            // Pass the structured data to the view
            return view('pages.insurence.property.application', [
                'applicationData' => $applicationData->toArray(),
            ]);
        } catch (\Exception $e) {
            return $this->handleGeneralError('property', $e, 'application');
        }
    }

    public function storage(): RedirectResponse
    {
        try {
            if (!session()->has('property_application_data')) {
                return $this->handleSessionNotFound('property');
            }

            $applicationData = session('property_application_data');
            $apiResponse = session('property_api_response');

            Log::info('Property storage: Creating order', [
                'has_application_data' => !empty($applicationData),
                'has_api_response' => !empty($apiResponse),
            ]);

            // Create order
            $orderData = [
                'product_name' => 'MOL-MULK Sug\'urta',
                'amount' => $applicationData['insurancePremium'] ?? 0,
                'state' => 0,
                'insurance_id' => $apiResponse['id'] ?? uniqid('prop_'),
                'phone' => $applicationData['applicant']['phoneNumber'] ?? null,
                'insurances_data' => $applicationData,
                'insurances_response_data' => $apiResponse,
                'status' => Order::STATUS_NEW,
            ];

            $order = $this->orderService->createOrder($orderData);

            Log::info('Property storage: Order created successfully', ['order_id' => $order->id]);

            return $this->redirectWithSuccess(
                'payment.show',
                ['locale' => getCurrentLocale(), 'orderId' => $order->id],
                __('success.insurance.order_created')
            );
        } catch (\Exception $e) {
            return $this->handleOrderCreationError('property', $e);
        }
    }

    public function calculation(): View
    {
        return view('pages.insurence.property.calculation');
    }

    /**
     * Fetch property data by cadaster number (AJAX endpoint)
     */
    public function fetchCadaster(Request $request): JsonResponse
    {
        $request->validate([
            'cadasterNumber' => ['required', 'string', 'regex:/^\d{2}:\d{2}:\d{2}:\d{2}:\d{2}:\d{4}$/'],
        ], [
            'cadasterNumber.required' => 'Kadastr raqami kiritilishi shart',
            'cadasterNumber.regex' => 'Kadastr raqami formati noto\'g\'ri (masalan: 11:11:10:01:03:0499)',
        ]);

        $result = $this->propertyService->fetchPropertyByCadaster($request->input('cadasterNumber'));

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? __('errors.insurance.property.cadaster_not_found'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'result' => $result['result'],
        ]);
    }
}
