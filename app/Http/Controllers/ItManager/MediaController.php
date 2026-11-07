<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    private const DISK_DIR = 'it-media';

    public function __construct()
    {
        $this->middleware(['auth', 'active', 'role:it_manager|admin']);
    }

    public function index(): View
    {
        $disk = Storage::disk('public');
        if (! $disk->exists(self::DISK_DIR)) {
            $disk->makeDirectory(self::DISK_DIR);
        }

        $files = collect($disk->files(self::DISK_DIR))
            ->filter(fn ($path) => preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $path))
            ->map(fn ($path) => [
                'path' => $path,
                'name' => basename($path),
                'url' => $disk->url($path),
                'size' => $disk->size($path),
                'updated_at' => $disk->lastModified($path),
            ])
            ->sortByDesc('updated_at')
            ->values();

        return view('it-manager.media.index', compact('files'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $file = $request->file('image');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = ($name !== '' ? $name : 'media').'-'.Str::lower(Str::random(6)).'.'.$file->getClientOriginalExtension();

        $file->storeAs(self::DISK_DIR, $name, 'public');

        return back()->with('success', 'Image « '.$name.' » ajoutée.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = $data['path'];
        if (! str_starts_with($path, self::DISK_DIR.'/')) {
            return back()->withErrors(['path' => 'Fichier non autorisé.']);
        }

        Storage::disk('public')->delete($path);

        return back()->with('success', 'Image supprimée.');
    }
}
