<?php

namespace App\Http\Controllers\ApiControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Api;

class DriverInfoController extends Controller
{
    use Api;
    public function getDriverInfo(Request $request)
    {
        $request->validate([
            'passport_series' => 'required|string',
            'passport_number' => 'required|string',
            'pinfl' => 'required|string'
        ]);

        $data = [
            'transactionId' => now()->timestamp,
            'isConsent' => 'Y',
            'pinfl' => $request->input('pinfl'),
            'document' => $request->input('passport_series') . $request->input('passport_number'),
            'senderPinfl' => $request->input('pinfl'),
        ];

        // $response = Http::post('https://impex-insurance.uz/api/fetch-driver-summary', $data);
        $response = $this->sendRequest('/api/provider/driver-summary-v2', $data);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $response->json(),
                'inputs' => $request->all()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $response->json(),
        ]);
    }
}
