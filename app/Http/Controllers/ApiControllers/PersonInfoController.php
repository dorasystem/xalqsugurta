<?php

namespace App\Http\Controllers\ApiControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class PersonInfoController extends Controller
{

    public function getPersonInfo(Request $request)
    {
        $request->validate([
            'senderPinfl' => 'required',
            'passport_series' => 'required',
            'passport_number' => 'required',
            'pinfl' => 'required',
            'isConsent' => 'required',
        ]);

        $data = [
            'senderPinfl' => $request->input('senderPinfl'),
            'passport_series' => strtoupper($request->input('passport_series')),
            'passport_number' => $request->input('passport_number'),
            'pinfl' => $request->input('pinfl'),
            'isConsent' => $request->input('isConsent')
        ];

        $response = Http::post('https://impex-insurance.uz/api/fetch-person-pinfl-v2', $data);

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
