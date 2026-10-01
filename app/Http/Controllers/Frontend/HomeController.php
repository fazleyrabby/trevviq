<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Location\DiscoveryService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(public DiscoveryService $discovery) {}

    public function index(): View
    {
        return view('frontend.home', [
            'popularCountries' => $this->discovery->popularCountries(),
            'popularCities' => $this->discovery->popularCities(),
            'trendingDestinations' => $this->discovery->trendingDestinations(),
            'recentlyAdded' => $this->discovery->recentlyAdded(),
            'hiddenGems' => $this->discovery->hiddenGems(),
            'upcomingEvents' => $this->discovery->upcomingEvents(),
        ]);
    }
}
