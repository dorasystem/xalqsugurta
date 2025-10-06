<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VehicleInfoController extends Controller
{

    public function getVehicleInfo(Request $request)
    {
        $request->validate([
            'gov_number' => 'required|alpha_num|min:8|max:8',
            'tech_passport_series' => 'required|alpha|min:3|max:3',
            'tech_passport_number' => 'required|min:7|max:7'
        ]);

        $data = [
            'gov' => substr($request->gov_number, 0, 2),
            'number' => substr($request->gov_number, 2),
            'techPassportSeria' => $request->tech_passport_series,
            'techPassportNumber' => $request->tech_passport_number
        ];

        $response = Http::post('https://impex-insurance.uz/api/fetch-vehicle-info', $data);

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
