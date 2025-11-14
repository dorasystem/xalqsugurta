<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurence\PersonBirthdate;
use Illuminate\Support\Facades\Http;

class NappApiController extends Controller
{
    public function getPersonInfoByBirthDate(PersonBirthdate $request)
    {
        $birthDate = $request->validated();
        $response = Http::get('https://napp.uz/api/v1/person-info', $birthDate);
        return response()->json($response->json());
    }
}
