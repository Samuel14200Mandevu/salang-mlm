<?php

namespace App\Http\Controllers\Admin\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\CashEntry;
use App\Models\Pharmacy\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from')?->toDateString()
            ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString()
            ?? now()->toDateString();

        $entries = CashEntry::query()
            ->with('user')
            ->whereBetween('entry_date', [$from, $to])
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $settings = Setting::current();
        $in = (float) CashEntry::query()->whereBetween('entry_date', [$from, $to])->sum('amount_in');
        $out = (float) CashEntry::query()->whereBetween('entry_date', [$from, $to])->sum('amount_out');
        $balance = round((float) $settings->cash_float + $in - $out, 2);

        return view('admin.pharmacy.cash.index', [
            'entries' => $entries,
            'from' => $from,
            'to' => $to,
            'cashFloat' => (float) $settings->cash_float,
            'periodIn' => $in,
            'periodOut' => $out,
            'balance' => $balance,
            'categories' => CashEntry::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'entry_date' => ['required', 'date'],
            'label' => ['required', 'string', 'max:200'],
            'category' => ['required', Rule::in(array_keys(CashEntry::CATEGORIES))],
            'amount_in' => ['nullable', 'numeric', 'min:0'],
            'amount_out' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $in = round((float) ($data['amount_in'] ?? 0), 2);
        $out = round((float) ($data['amount_out'] ?? 0), 2);
        if ($in <= 0 && $out <= 0) {
            return back()->withInput()->with('error', 'Indiquez un montant entrant ou sortant.');
        }

        CashEntry::query()->create([
            'entry_date' => $data['entry_date'],
            'label' => $data['label'],
            'category' => $data['category'],
            'amount_in' => $in,
            'amount_out' => $out,
            'notes' => $data['notes'] ?? null,
            'user_id' => $request->user()?->id,
        ]);

        return back()->with('success', 'Mouvement de caisse enregistré.');
    }

    public function updateFloat(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cash_float' => ['required', 'numeric', 'min:0'],
        ]);

        $settings = Setting::current();
        $settings->update(['cash_float' => round((float) $data['cash_float'], 2)]);

        return back()->with('success', 'Fond de caisse mis à jour.');
    }
}
