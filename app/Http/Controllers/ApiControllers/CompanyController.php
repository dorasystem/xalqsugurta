<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Traits\Api;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use Api;

    public function getCompanyInfo(Request $request)
    {
        $request->validate([
            'inn' => 'required|string',
            'product_name' => 'required|string',
        ]);

        $data = [
            'inn' => $request->inn,
        ];

        $response = $this->sendRequest('/api/provider/inn', $data);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $response->json(),
                'inputs' => $request->all()
            ]);
        }

        session([$request->product_name => $response->json()]);

        return response()->json([
            'success' => true,
            'data' => $response->json(),
        ]);
    }
}
