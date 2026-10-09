<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Support\Facades\Schema;

class ServicesController extends Controller
{
    public function index()
    {
        $servicesFeaturedPublications = collect();

        if (Schema::hasTable('publications')) {
            $servicesFeaturedPublications = Publication::query()
                ->active()
                ->with('medias')
                ->latest()
                ->limit(8)
                ->get();
        }

        return view('services.index', [
            'servicesFeaturedPublications' => $servicesFeaturedPublications,
        ]);
    }
}
