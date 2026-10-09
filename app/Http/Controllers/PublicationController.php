<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\Publication;
use App\Models\PublicationMedia;
use App\Services\PublicationMediaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicationController extends Controller
{
    public function __construct(
        protected PublicationMediaStorage $mediaStorage
    ) {
        $this->middleware('role:admin|it_manager')->only([
            'create', 'store', 'edit', 'update', 'destroy', 'deleteMedia',
        ]);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Publication::class);

        $user = Auth::user();
        $query = Publication::query()
            ->with(['user', 'medias'])
            ->latest();

        if (! $user->hasSupervisionAccess()) {
            $query->published();
        }

        if ($request->filled('type') && in_array($request->type, [Publication::TYPE_EVENT, Publication::TYPE_PROMOTION], true)) {
            $query->where('type', $request->type);
        }

        if ($request->boolean('active')) {
            $query->active();
        }

        $publications = $query->paginate(12)->withQueryString();

        $servicesFeaturedPublications = collect();
        if (! $user->hasSupervisionAccess()) {
            $servicesFeaturedPublications = Publication::query()
                ->active()
                ->with('medias')
                ->latest()
                ->limit(8)
                ->get();
        }

        return view('publications.index', [
            'publications' => $publications,
            'isSupervision' => $user->hasSupervisionAccess(),
            'typeFilter' => $request->get('type'),
            'activeFilter' => $request->boolean('active'),
            'servicesFeaturedPublications' => $servicesFeaturedPublications,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Publication::class);

        return view('publications.create');
    }

    public function store(StorePublicationRequest $request)
    {
        $data = $request->validated();
        unset($data['medias']);

        $publication = Publication::create([
            ...$data,
            'user_id' => Auth::id(),
            'is_published' => $request->boolean('is_published', true),
        ]);

        if ($request->hasFile('medias')) {
            $this->mediaStorage->storeMany($publication, $request->file('medias'));
        }

        return redirect()
            ->route('publications.show', $publication)
            ->with('success', 'Publication créée.');
    }

    public function show(Publication $publication)
    {
        $this->authorize('view', $publication);

        $publication->load(['user', 'medias']);

        return view('publications.show', [
            'publication' => $publication,
            'isSupervision' => Auth::user()->hasSupervisionAccess(),
        ]);
    }

    public function edit(Publication $publication)
    {
        $this->authorize('update', $publication);

        $publication->load('medias');

        return view('publications.edit', compact('publication'));
    }

    public function update(UpdatePublicationRequest $request, Publication $publication)
    {
        $data = $request->validated();
        unset($data['medias']);

        $publication->update([
            ...$data,
            'is_published' => $request->boolean('is_published', $publication->is_published),
        ]);

        if ($request->hasFile('medias')) {
            $this->mediaStorage->storeMany($publication, $request->file('medias'));
        }

        return redirect()
            ->route('publications.show', $publication)
            ->with('success', 'Publication mise à jour.');
    }

    public function destroy(Publication $publication)
    {
        $this->authorize('delete', $publication);

        $publication->delete();

        return redirect()
            ->route('publications.index')
            ->with('success', 'Publication supprimée.');
    }

    public function deleteMedia(PublicationMedia $media)
    {
        $this->authorize('deleteMedia', $media);

        $publication = $media->publication;
        $media->delete();

        return redirect()
            ->route('publications.edit', $publication)
            ->with('success', 'Média supprimé.');
    }
}
