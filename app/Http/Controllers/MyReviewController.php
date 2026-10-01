<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MyReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = $request->user()->reviews()
            ->with('location')
            ->latest()
            ->paginate(10);

        return view('frontend.reviews.index', ['reviews' => $reviews]);
    }
}
