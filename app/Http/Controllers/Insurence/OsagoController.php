<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OsagoController extends Controller
{
    public function main(): View
    {
        return view('pages.insurence.main');
    }


    public function application(): View
    {
        return view('pages.insurence.application');
    }

    public function payment(): View
    {
        return view('pages.insurence.payment');
    }
}
