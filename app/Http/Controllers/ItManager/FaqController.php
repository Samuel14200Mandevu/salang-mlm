<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use App\Models\MlmSetting;
use App\Services\SiteContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __construct(
        protected SiteContentService $content
    ) {
        $this->middleware(['auth', 'active', 'role:it_manager|admin']);
    }

    public function edit(): View
    {
        $faqs = $this->content->faqs();
        if ($faqs === []) {
            $faqs = [['question' => '', 'answer' => '']];
        }

        return view('it-manager.faq.edit', compact('faqs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'faqs' => ['required', 'array', 'max:40'],
            'faqs.*.question' => ['nullable', 'string', 'max:255'],
            'faqs.*.answer' => ['nullable', 'string', 'max:2000'],
        ]);

        $faqs = collect($data['faqs'] ?? [])
            ->map(fn ($row) => [
                'question' => trim((string) ($row['question'] ?? '')),
                'answer' => trim((string) ($row['answer'] ?? '')),
            ])
            ->filter(fn ($row) => $row['question'] !== '' && $row['answer'] !== '')
            ->values()
            ->all();

        MlmSetting::setValue('member_faqs', $faqs, 'help', 'FAQ centre d’aide & assistant');

        return back()->with('success', count($faqs).' question(s) FAQ enregistrée(s).');
    }
}
