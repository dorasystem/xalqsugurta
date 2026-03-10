<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Traits\Api;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    use Api;

    public function getRegions()
    {
        $response = $this->sendRequest('/api/references/regions', [], 'GET');
        $regions = json_decode($response->body(), true);
        return $regions['result'] ?? [];
    }

    public function getDistricts(Request $request)
    {
        $response = $this->sendRequest('/api/references/districts', [], 'GET');
        $districts = json_decode($response->body(), true);
        return response()->json([
            'success' => true,
            'data' => $districts['result'] ?? [],
        ]);
    }
}
