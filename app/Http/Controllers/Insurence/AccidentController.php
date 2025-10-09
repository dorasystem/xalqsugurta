<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccidentController extends Controller
{
    public function main()
    {
        return view('pages.insurence.accident.main');
    }

    public function application()
    {
        return view('pages.insurence.accident.application');
    }

    public function payment()
    {
        return view('pages.insurence.accident.payment');
    }

    public function calculation()
    {
        return view('pages.insurence.accident.calculation');
    }
}
