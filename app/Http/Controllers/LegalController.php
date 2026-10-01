<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        return view('frontend.legal.terms');
    }

    public function privacy(): View
    {
        return view('frontend.legal.privacy');
    }

    public function acceptableUse(): View
    {
        return view('frontend.legal.acceptable-use');
    }

    public function dmca(): View
    {
        return view('frontend.legal.dmca');
    }
}
