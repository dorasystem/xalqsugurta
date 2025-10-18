<?php

namespace App\Http\Controllers\Payments\Click;

use App\Models\Order;
use App\Models\ClickUz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ClickController extends Controller
{
    public function prepare(Request $request)
    {

        Log::info('Prepare', [$request->all()]);
        $clickTransId = $request->input('Request.click_trans_id');
        $serviceId = $request->input('Request.service_id');
        $clickPaydocId = $request->input('Request.click_paydoc_id');
        $merchantTransId = $request->input('Request.merchant_trans_id');
        $amount = $request->input('Request.amount');
        $action = $request->input('Request.action');
        $error = $request->input('Request.error');
        $errorNote = $request->input('Request.error_note');
        $signTime = $request->input('Request.sign_time');
        //        $signString = $request->input('Request.sign_string');
        //        $secretKey = 'cENzWeHyuR';
        //        $generatedSignString = md5($clickTransId . $serviceId . $secretKey . $merchantTransId . $amount . $action . $signTime);
        //         dd($generatedSignString);
        //        dd($generatedSignString, $signString);
        //        if ($signString !== $generatedSignString) {
        //            return response()->json(['error' => -1, 'error_note' => 'Invalid sign_string']);
        //        }
        //        \Log::info('Click Prepare Response:', $signString);

        ClickUz::create([
            'click_trans_id' => $clickTransId,
            'merchant_trans_id' => $merchantTransId,
            'amount' => $amount,
            'amount_rub' => $amount,
            'sign_time' => $signTime,
            'situation' => $error
        ]);

        if ($error == 0) {
            $response = [
                'click_trans_id' => $clickTransId,
                'merchant_trans_id' => $merchantTransId,
                'merchant_prepare_id' => $merchantTransId,
                'error' => 0,
                'error_note' => 'Payment prepared successfully',
            ];
        } else {
            $response = [
                'click_trans_id' => $clickTransId,
                'merchant_trans_id' => $merchantTransId,
                'merchant_prepare_id' => $merchantTransId,
                'error' => -9,
                'error_note' => 'Do not find a user!!!',
            ];
        }

        Log::info('Click Prepare Response:', $response);

        return response()->json($response);
    }
    public function complete(Request $request)
    {
        $clickTransId = $request->input('Request.click_trans_id');
        $serviceId = $request->input('Request.service_id');
        $clickPaydocId = $request->input('Request.click_paydoc_id');
        $merchantTransId = $request->input('Request.merchant_trans_id');
        $merchantPrepareId = $request->input('Request.merchant_prepare_id');
        $amount = $request->input('Request.amount');
        $action = $request->input('Request.action');
        $error = $request->input('Request.error');
        //        $errorNote = $request->input('Request.error_note');
        $signTime = $request->input('Request.sign_time');
        //        $signString = $request->input('Request.sign_string');
        //        $secretKey = 'cENzWeHyuR';
        // $secretKey = env('MERCHANT_KEY');

        //        $generatedSignString = md5($clickTransId . $serviceId . $secretKey . $merchantTransId . $merchantPrepareId . $amount . $action . $signTime);
        //        dd($generatedSignString, $signString);
        //        if ($signString !== $generatedSignString) {
        //            return response()->json(['error' => -1, 'error_note' => 'Invalid sign_string']);
        //        }

        if ($error == 0) {
            ClickUz::where('click_trans_id', $clickTransId)->update(['situation' => 1, 'status' => 'success']);
            Order::where('id', $merchantTransId)->update(['status' => 'yakunlandi']);
            $order = Order::find($merchantTransId);

            $polisUuid = $order->insurances_response_data['response']['result']['policies'][0]['uuid'] ?? null;
            $insurancesData = $order->insurances_data;

            $startDate = $insurancesData[0]['policies'][0]['startDate'] ?? null;
            $endDate = $insurancesData[0]['policies'][0]['endDate'] ?? null;
            // Order ma'lumotlarini olish
            $info = [
                "polisUuid" => $polisUuid, // API javobidan olish
                "paidAt" => now()->toDateTimeString(),
                "insurancePremium" => $order->amount, // Orderdagi sug‘urta summasi
                "startDate" => $startDate,
                "endDate" => $endDate,
                "comission" => 0,
                "agencyId" => "28"
            ];
            $response = Http::post('https://impex-insurance.uz/api/confirm-payed', $info);
            $responseData = $response->json();

            $order->update([
                'confirm_payed_data' => $info,
                'confirm_payed_response_data' => $responseData,
            ]);
            return response()->json([
                'click_trans_id' => $clickTransId,
                'merchant_trans_id' => $merchantTransId,
                'merchant_confirm_id' => $merchantTransId,
                'error' => 0,
                'error_note' => 'Payment Success'
            ]);
        } else {
            ClickUz::where('click_trans_id', $clickTransId)->update(['situation' => -9, 'status' => 'error']);
            Order::where('id', $merchantTransId)->update(['status' => 'bekor qilingan']);
            return response()->json([
                'click_trans_id' => $clickTransId,
                'merchant_trans_id' => $merchantTransId,
                'merchant_confirm_id' => $merchantTransId,
                'error' => -9,
                'error_note' => 'Do not find a user!!!'
            ]);
        }
    }
}
