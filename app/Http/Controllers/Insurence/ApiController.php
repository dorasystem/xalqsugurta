<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function fetchVehicleInfo(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
            ])->post('https://impex-insurance.uz/api/fetch-vehicle-info', $request->all());

            if ($response->failed()) {
                return response()->json(['error' => 'API bilan bog‘lanishda xatolik'], $response->status());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server xatosi', 'message' => $e->getMessage()], 500);
        }
    }
}
