<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\CareerQualificationPublicationsSync;
use Illuminate\Support\Facades\Schema;

class ServicesController extends Controller
{
    public function __construct(
        protected CareerQualificationPublicationsSync $careerPublicationsSync
    ) {}

    public function index()
    {
        $servicesFeaturedPublications = collect();

        if (Schema::hasTable('publications')) {
            $this->careerPublicationsSync->sync();
            $servicesFeaturedPublications = Publication::query()
                ->active()
                ->with('medias')
                ->orderByRaw("CASE WHEN title LIKE 'Bonus carrière%' THEN 0 ELSE 1 END")
                ->latest()
                ->limit(12)
                ->get();
        }

        return view('services.index', [
            'servicesFeaturedPublications' => $servicesFeaturedPublications,
        ]);
    }
}
