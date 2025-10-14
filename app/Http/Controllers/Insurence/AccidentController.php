<?php

namespace App\Http\Controllers\Insurence;

use App\Actions\Insurence\ProcessAccidentApplicationAction;
use App\DTOs\AccidentApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\AccidentApplicationRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class AccidentController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function main(): View
    {
        return view('pages.insurence.accident.main');
    }

    public function applicationView(): View|RedirectResponse
    {
        // For GET requests, we need to handle the case where there's no data
        // This could happen if someone directly accesses the URL
        // We'll redirect to main page if no session data exists
        if (!session()->has('accident_application_data')) {
            return redirect()->route('accident.main', ['locale' => getCurrentLocale()])
                ->with('error', 'Ariza ma\'lumotlari topilmadi. Iltimos, qaytadan ariza to\'ldiring.');
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
            return back()
                ->withErrors(['error' => 'Xatolik yuz berdi: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function storage(): RedirectResponse
    {
        try {
            // Get application data from session
            if (!session()->has('accident_application_data')) {
                \Log::warning('Accident storage: Session data not found');
                return redirect()->route('accident.main', ['locale' => getCurrentLocale()])
                    ->with('error', 'Ariza ma\'lumotlari topilmadi. Iltimos, qaytadan ariza to\'ldiring.');
            }

            $applicationData = session('accident_application_data');
            $apiResponse = session('accident_api_response');

            \Log::info('Accident storage: Creating order', [
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

            \Log::info('Accident storage: Order created successfully', ['order_id' => $order->id]);

            // Redirect to payment page with orderId
            return redirect()->route('accident.payment', [
                'locale' => getCurrentLocale(),
                'orderId' => $order->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Accident storage: Failed to create order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', 'Buyurtma yaratishda xatolik: ' . $e->getMessage());
        }
    }

    public function payment(int $orderId): View|RedirectResponse
    {
        try {
            $order = $this->orderService->getOrderById($orderId);

            if (!$order) {
                return redirect()->route('accident.main', ['locale' => getCurrentLocale()])
                    ->with('error', 'Buyurtma topilmadi.');
            }

            return view('pages.insurence.accident.payment', [
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('accident.main', ['locale' => getCurrentLocale()])
                ->with('error', 'Xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    public function calculation(): View
    {
        return view('pages.insurence.accident.calculation');
    }
}
