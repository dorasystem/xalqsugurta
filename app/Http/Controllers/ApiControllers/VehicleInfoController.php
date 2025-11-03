<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Traits\Api;
use Illuminate\Http\Request;

class VehicleInfoController extends Controller
{
    use Api;

    public function getVehicleInfo(Request $request)
    {
        $request->validate([
            'gov_number' => 'required|alpha_num|min:8|max:8',
            'tech_passport_series' => 'required|alpha|min:3|max:3',
            'tech_passport_number' => 'required|min:7|max:7'
        ]);

        $data = [
            'govNumber' => $request->gov_number,
            'techPassportSeria' => $request->tech_passport_series,
            'techPassportNumber' => $request->tech_passport_number
        ];

        // $response = Http::post('https://impex-insurance.uz/api/fetch-vehicle-info', $data);

        $response = $this->sendRequest('/api/provider/osago/vehicle', $data);

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
