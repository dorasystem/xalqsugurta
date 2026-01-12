<?php

namespace App\Http\Controllers\Insurence;

use App\DTOs\PropertyApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\PropertyApplicationRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PropertyService;
use App\Traits\Api;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class PropertyController extends Controller
{
    use Api, HandlesInsuranceErrors;

    public function __construct(
        private readonly PropertyService $propertyService,
        private readonly OrderService $orderService
    ) {
    }

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
        PropertyApplicationRequest $request
    ): View|RedirectResponse {
        try {
            // Create DTO from validated request data
            $applicationData = PropertyApplicationData::fromRequest($request->validated());

            // Send data to API using Api trait
            $requestData = $applicationData->toApiFormat();

            Log::info('Property application: Sending request to API', [
                'has_applicant' => !empty($requestData['insurant']['person']['fullName']['firstname']),
                'has_owner' => !empty($requestData['policies'][0]['objects'][0]['others']['ownerPerson']['fullName']['firstname']),
                'insurance_amount' => $requestData['sum'] ?? 0,
            ]);

            // Use Api trait to send request
            $response = $this->sendRequest('/api/provider/property-insurance', $requestData);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = is_array($errorData)
                    ? json_encode($errorData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                    : (string) ($errorData['message'] ?? $response->body());

                Log::error('Property application: API returned error', [
                    'status' => $response->status(),
                    'error' => $errorData,
                    'formatted_error' => $errorMessage,
                ]);

                return back()
                    ->withErrors(['error' => $errorMessage])
                    ->withInput();
            }

            $responseData = $response->json();

            Log::info('Property application: API response received', [
                'has_data' => !empty($responseData),
            ]);

            // Store data in session for GET requests (language switching and order creation)
            session([
                'property_application_data' => $applicationData->toArray(),
                'property_api_response' => $responseData ?? null,
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

            // Extract insurance ID from API response (handle different response formats)
            $insuranceId = $apiResponse['id']
                ?? $apiResponse['UUID']
                ?? ($apiResponse['response']['result']['contractUuid'] ?? null)
                ?? uniqid('prop_');

            // Extract contract UUID if available
            $contractUuid = $apiResponse['UUID']
                ?? ($apiResponse['response']['result']['contractUuid'] ?? null)
                ?? ($apiResponse['contractUuid'] ?? null);

            // Create order
            $orderData = [
                'product_name' => 'MOL-MULK Sug\'urta',
                'amount' => $applicationData['insurancePremium'] ?? 0,
                'state' => 0,
                'insurance_id' => $insuranceId,
                'phone' => $applicationData['applicant']['phoneNumber'] ?? null,
                'insurances_data' => $applicationData,
                'insurances_response_data' => $apiResponse,
                'status' => Order::STATUS_NEW,
                'contractStartDate' => $applicationData['paymentStartDate'] ?? null,
                'contractEndDate' => $applicationData['paymentEndDate'] ?? null,
                'insuranceProductName' => 'MOL-MULK Sug\'urta',
                'polic_id_number' => $contractUuid,
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
            'product_name' => ['required', 'string'],
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

        session([$request->input('product_name') => $result['result']]);

        return response()->json([
            'success' => true,
            'result' => $result['result'],
        ]);
    }
}
