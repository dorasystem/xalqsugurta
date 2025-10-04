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
            'gov_number' => 'required',
            'tech_passport_series' => 'required',
            'tech_passport_number' => 'required'
        ]);

        $data = [
            'gov' => substr($request->gov_number, 0, 2),
            'number' => substr($request->gov_number, 2),
            'techPassportSeria' => $request->tech_passport_series,
            'techPassportNumber' => $request->tech_passport_number
        ];

        $response = Http::post('https://impex-insurance.uz/api/fetch-vehicle-info',$data);

        return $response->json();
    }
}
