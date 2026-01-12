<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Traits\Api;
use Illuminate\Http\Request;

class PropertyInfoController extends Controller
{
    use Api;


    public function fetchPropertyInfo(Request $request)
    {
        $request->validate([
            'cadasterNumber' => 'required|string',
            'product_name' => 'required|string',
        ]);

        $data = $this->sendRequest('/api/provider/cadaster', [
            'cadasterNumber' => $request->cadasterNumber,
        ]);

        if ($data->failed()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $data->json(),
                'inputs' => $request->all()
            ]);
        }

        session([$request->product_name => $data->json('result')]);

        return response()->json([
            'success' => true,
            'result' => $data->json('result'),
            'data' => $data->json(),
        ]);
    }
}
