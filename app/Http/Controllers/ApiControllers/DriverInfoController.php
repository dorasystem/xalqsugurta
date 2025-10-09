<?php

namespace App\Http\Controllers\ApiControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class DriverInfoController extends Controller
{
    public function getDriverInfo(Request $request)
    {
        $request->validate([
            'passport_series' => 'required|string',
            'passport_number' => 'required|string',
            'pinfl' => 'required|string'
        ]);
        
        $data = [
            'pinfl' => $request->input('pinfl'),
            'passportSeries' => $request->input('passport_series'),
            'passportNumber' => $request->input('passport_number'),
        ];

        $response = Http::post('https://impex-insurance.uz/api/fetch-driver-summary', $data);

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
