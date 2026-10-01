<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LocationType;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationImportBatch;
use Illuminate\View\View;

class ImportBatchController extends Controller
{
    public function index(): View
    {
        $batches = LocationImportBatch::query()->latest()->paginate(20);

        $stats = [
            'locations' => Location::count(),
            'indexable' => Location::where('indexable', true)->count(),
            'countries' => Location::where('type', LocationType::Country)->count(),
            'cities' => Location::where('type', LocationType::City)->count(),
        ];

        return view('admin.imports.index', [
            'batches' => $batches,
            'stats' => $stats,
        ]);
    }
}
