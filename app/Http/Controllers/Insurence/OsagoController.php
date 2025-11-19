<?php

namespace App\Http\Controllers\Insurence;

use App\DTOs\OsagoApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\Osago\OsagoApplicationRequest;
use App\Models\Order;
use App\Services\InsuranceApiService;
use App\Services\OrderService;
use App\Services\OsagoPriceCalculator;
use App\Traits\Api;
use App\Traits\HandlesInsuranceErrors;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

final class OsagoController extends Controller
{
    use HandlesInsuranceErrors;
    use Api;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly OsagoPriceCalculator $priceCalculator,
        private readonly InsuranceApiService $apiService
    ) {}

    public function main(): View
    {
        return view('pages.insurence.osago.main');
    }

    public function application(OsagoApplicationRequest $request): View|RedirectResponse
    {
        try {
            // Create DTO from validated request data
            $applicationData = OsagoApplicationData::fromRequest($request->validated());

            // Store data in session for GET requests (language switching and order creation)
            session([
                'osago_application_data' => $applicationData->toArray(),
            ]);

            // Redirect to application page
            return redirect()->route('osago.application.view', ['locale' => getCurrentLocale()]);
        } catch (\Exception $e) {
            return $this->handleGeneralError('osago', $e, 'application');
        }
    }

    public function applicationView(): View|RedirectResponse
    {
        if (!session()->has('osago_application_data')) {
            return $this->handleSessionNotFound('osago');
        }

        $applicationData = session('osago_application_data');
        return view('pages.insurence.osago.application', [
            'data' => $applicationData,
        ]);
    }

    public function storage(): RedirectResponse
    {
        try {
            if (!session()->has('osago_application_data')) {
                return $this->handleSessionNotFound('osago');
            }

            $applicationData = session('osago_application_data');

            // Drivers decode qilish
            $drivers = [];
            if (!empty($applicationData['drivers']) && is_array($applicationData['drivers'])) {
                foreach ($applicationData['drivers'] as $driverJson) {
                    $decoded = is_string($driverJson) ? json_decode($driverJson, true) : $driverJson;
                    if ($decoded) $drivers[] = $decoded;
                }
            }
            $applicationData['drivers'] = $drivers;

            // DTO yaratamiz
            $dataDTO = new OsagoApplicationData(
                applicant: $applicationData['applicant'],
                owner: $applicationData['owner'],
                details: $applicationData['details'],
                cost: $applicationData['cost'],
                vehicle: $applicationData['vehicle'],
                drivers: $applicationData['drivers'],
                govNumber: $applicationData['govNumber'],
                insurancePremium: $applicationData['insurancePremium'],
            );

            // API formatga o‘tkazamiz
            $requestData = $dataDTO->toApiFormat();

            $result = $this->sendRequest('', $requestData, '/doraosago/create');
            // dd($requestData);
            $response = $result->json();
            $apiResponse = $result->json();


            Log::info('OSAGO API Request:', $requestData);
            Log::info('OSAGO API Response:', $response);

            if (!$result->successful()) {
                // server tomonidan yuborilgan xabarni aniq chiqarish
                $msg = data_get($apiResponse, 'result_message', $result->body());
                return back()->withErrors(['error' => 'API xatosi: ' . $msg])->withInput();
            }

            if (isset($apiResponse['result']) && $apiResponse['result'] != 0) {
                $msg = $apiResponse['result_message'] ?? 'Unknown API error';
                Log::error('OSAGO API returned error code: ' . $apiResponse['result'] . ' — ' . $msg);
                return back()->withErrors(['error' => 'API xatosi: ' . $msg])->withInput();
            }

            if (!$result->successful()) {
                return back()->withErrors([
                    'error' => $response['result_message'] ?? 'API xatosi'
                ]);
            }

            $apiResponse = $result['data'] ?? [];

            // APIdan polic_id_number olish
            $applicationData['polic_id_number'] = $apiResponse['response']['result']['polic_id_number'] ?? '';

            // Price tekshirish (security)
            $finalPriceCheck = $this->priceCalculator->verifyPrice(
                submittedAmount: $applicationData['insurancePremium'],
                govNumber: $applicationData['govNumber'],
                vehicleTypeId: $applicationData['vehicle']['typeId'],
                period: $applicationData['cost']['contractTermConclusionId'],
                driverLimit: $applicationData['details']['driverNumberRestriction'] ? 'limited' : 'unlimited',
                tolerance: 100
            );

            if (!$finalPriceCheck) {
                $correctPrice = $this->priceCalculator->calculate(
                    govNumber: $applicationData['govNumber'],
                    vehicleTypeId: $applicationData['vehicle']['typeId'],
                    period: $applicationData['cost']['contractTermConclusionId'],
                    driverLimit: $applicationData['details']['driverNumberRestriction'] ? 'limited' : 'unlimited'
                );
                $applicationData['insurancePremium'] = $correctPrice['amount'];
                $applicationData['cost']['insurancePremium'] = $correctPrice['amount'];
                $applicationData['cost']['insurancePremiumPaidToInsurer'] = $correctPrice['amount'];
            }

            // Order yaratish
            $order = $this->orderService->createOrder([
                'product_name' => __('insurance.osago.product_name'),
                'amount' => $applicationData['insurancePremium'] ?? 0,
                'state' => 0,
                'insurance_uuid' => $apiResponse['response']['result']['uuid'] ?? uniqid('osago_'),
                'phone' => $applicationData['applicant']['person']['phoneNumber'] ?? null,
                'insurances_data' => $applicationData,
                'insurances_response_data' => $apiResponse,
                'status' => Order::STATUS_NEW,
                'contractStartDate' => data_get($applicationData, 'details.startDate'),
                'contractEndDate' => data_get($applicationData, 'details.endDate'),
            ]);

            // session()->forget('osago_application_data');

            return $this->redirectWithSuccess(
                'payment.show',
                ['locale' => getCurrentLocale(), 'orderId' => $order->id],
                __('success.insurance.order_created')
            );
        } catch (\Exception $e) {
            return $this->handleOrderCreationError('osago', $e);
        }
    }



    public function payment($lang, Order $order): View
    {
        return view('pages.insurence.payment', compact('order'));
    }
}
