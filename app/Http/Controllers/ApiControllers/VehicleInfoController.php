<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehicleInfoController extends Controller
{
    
    public function getVehicleInfo(Request $request)
    {
        return $request->json();
        dd($request->all());
    }

}
