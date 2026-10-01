<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\LocationType;
use App\Http\Controllers\Controller;
use App\Services\Location\LocationSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));

        $rawType = $request->query('type');
        $type = is_string($rawType) ? LocationType::tryFrom($rawType) : null;

        $results = app(LocationSearchService::class)->search($term, $type);

        return view('frontend.search.index', ['results' => $results, 'term' => $term]);
    }
}
