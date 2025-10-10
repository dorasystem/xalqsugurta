<?php

namespace App\Http\Controllers\ApiControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PersonPinflRequest;
use App\Http\Requests\Insurence\PersonBirthdateRequest;
use Illuminate\Support\Facades\Http;

class PersonInfoController extends Controller
{

    public function getPersonInfo(PersonPinflRequest $request)
    {
        $personData = $request->validated();

        $data = [
            'senderPinfl' => $personData['senderPinfl'],
            'passport_series' => strtoupper($personData['passport_series']),
            'passport_number' => $personData['passport_number'],
            'pinfl' => $personData['pinfl'],
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

    public function getPersonInfoByBirthdate(PersonBirthdateRequest $request)
    {
        $personData = $request->validated();

        $data = [
            'isConsent' => 'Y',
            'senderPinfl' => "50101005690010",
            'passport_series' => strtoupper($personData['passport_series']),
            'passport_number' => $personData['passport_number'],
            'birthDate' => $personData['birthDate'],
        ];

        $response = Http::post('https://impex-insurance.uz/api/fetch-brith-date-v2', $data);

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
            'result' => $response->json('result'),
            'data' => $response->json(),
        ]);
    }
}
