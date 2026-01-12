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

        $responseData = $response->json();

        if (isset($responseData['result'])) {
            session(['osago_person_info' => $responseData['result']]);
        }

        return response()->json([
            'success' => true,
            'data' => $response->json(),
        ]);
    }

    public function getPersonInfoByBirthdate(PersonBirthdateRequest $request)
    {
        $personData = $request->validated();

        $requestData = [
            'isConsent' => 'Y',
            'senderPinfl' => "31004603480032",
            'document' => strtoupper($personData['passport_series']) . $personData['passport_number'],
            'birthDate' => $personData['birthDate'],
            'transactionId' => (string) now()->timestamp,
        ];

        $response = $this->sendRequest('/api/provider/passport-birth-date-v2', $requestData);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $response->json(),
                'inputs' => $request->all()
            ], $response->status());
        }

        $responseData = $response->json();

        // Save to session if result exists
        if (isset($responseData['result']) && isset($personData['product_name'])) {
            session([$personData['product_name'] => $responseData['result']]);
        }

        return response()->json([
            'success' => true,
            'result' => $responseData['result'] ?? null,
            'data' => $responseData,
        ]);
    }
}
