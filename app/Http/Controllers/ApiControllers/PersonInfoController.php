<?php

namespace App\Http\Controllers\ApiControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PersonPinflRequest;
use App\Http\Requests\Insurence\PersonBirthdateRequest;
use Illuminate\Support\Facades\Http;
use App\Traits\Api;

class PersonInfoController extends Controller
{
    use Api;
    public function getPersonInfo(PersonPinflRequest $request)
    {
        $personData = $request->validated();

        $data = [
            'document' => strtoupper($personData['passport_series']) . $personData['passport_number'],
            'pinfl' => $personData['pinfl'],
            'senderPinfl' => $personData['senderPinfl'],
            'isConsent' => 'Y',
            'transactionId' => now()->timestamp,
        ];

        // {
        //     "transactionId": "string",
        //     "isConsent": "string",
        //     "senderPinfl": "string",
        //     "document": "string",
        //     "pinfl": "string"
        //   }

        $response = $this->sendRequest('/api/provider/pinfl-v2', $data);

        // $response = Http::post('https://impex-insurance.uz/api/fetch-person-pinfl-v2', $data);

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
            'document' => strtoupper($personData['passport_series']) . $personData['passport_number'],
            'birthDate' => $personData['birthDate'],
            'transactionId' => now()->timestamp,
        ];

        dd($data);

        $response = $this->sendRequest('/api/provider/passport-birth-date-v2', $data);
        // $response = Http::post('https://impex-insurance.uz/api/fetch-brith-date-v2', $data);

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
