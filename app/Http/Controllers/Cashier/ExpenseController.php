<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
        
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
                abort(403, 'Accès réservé aux caissiers et administrateurs.');
            }
            return $next($request);
        });
    }

    /**
     * Liste des dépenses avec filtres
     */
    public function index(Request $request)
    {
        $query = Expense::with(['user', 'relatedUser'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Un caissier ne voit que ses propres dépenses, l'admin voit tout
        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }

        // Filtres
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('reference', 'LIKE', "%{$search}%");
            });
        }

        $expenses = $query->paginate(20)->withQueryString();

        // Statistiques
        $baseQuery = Expense::approved();
        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            $baseQuery->where('user_id', auth()->id());
        }

        $stats = [
            'today' => (clone $baseQuery)->today()->sum('amount'),
            'month' => (clone $baseQuery)->thisMonth()->sum('amount'),
            'year' => (clone $baseQuery)->thisYear()->sum('amount'),
            'count_month' => (clone $baseQuery)->thisMonth()->count(),
        ];

        $categories = Expense::getCategories();

        return view('cashier.expenses.index', compact('expenses', 'stats', 'categories'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $categories = Expense::getCategories();
        $paymentMethods = Expense::getPaymentMethods();
        $currencies = Expense::getCurrencies();

        return view('cashier.expenses.create', compact('categories', 'paymentMethods', 'currencies'));
    }

    /**
     * Enregistrer une dépense
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_type' => 'required|in:caisse,membre',
            'related_user_id' => 'nullable|exists:users,id',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:USD,CDF',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,mobile_money,bank,autre',
            'reference' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // Si type = membre et pas de related_user_id, refuser
            if ($validated['expense_type'] === 'membre' && empty($validated['related_user_id'])) {
                return back()->withErrors(['related_user_id' => 'Veuillez sélectionner un membre.'])->withInput();
            }

            // Upload du justificatif
            $receiptImage = null;
            if ($request->hasFile('receipt_image')) {
                $file = $request->file('receipt_image');
                $filename = 'expense_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('expenses', $filename, 'public');
                $receiptImage = $filename;
            }

            $expense = Expense::create([
                'user_id' => auth()->id(),
                'expense_type' => $validated['expense_type'],
                'related_user_id' => $validated['related_user_id'] ?? null,
                'category' => $validated['category'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'expense_date' => $validated['expense_date'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'receipt_image' => $receiptImage,
                'status' => 'approved', // Statut direct
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            Log::info('Dépense créée', [
                'expense_id' => $expense->id,
                'user_id' => auth()->id(),
                'amount' => $expense->amount,
                'currency' => $expense->currency,
            ]);

            return redirect()
                ->route('cashier.expenses.index')
                ->with('success', 'Dépense enregistrée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création dépense: ' . $e->getMessage());

            return back()
                ->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Détails d'une dépense
     */
    public function show($id)
    {
        $expense = Expense::with(['user', 'relatedUser', 'approver'])->findOrFail($id);

        // Un caissier ne peut voir que ses propres dépenses
        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            if ($expense->user_id !== auth()->id()) {
                abort(403);
            }
        }

        return view('cashier.expenses.show', compact('expense'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $expense = Expense::findOrFail($id);

        // Un caissier ne peut modifier que ses propres dépenses
        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            if ($expense->user_id !== auth()->id()) {
                abort(403);
            }
        }

        $categories = Expense::getCategories();
        $paymentMethods = Expense::getPaymentMethods();
        $currencies = Expense::getCurrencies();

        return view('cashier.expenses.edit', compact('expense', 'categories', 'paymentMethods', 'currencies'));
    }

    /**
     * Mettre à jour une dépense
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        // Un caissier ne peut modifier que ses propres dépenses
        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            if ($expense->user_id !== auth()->id()) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'expense_type' => 'required|in:caisse,membre',
            'related_user_id' => 'nullable|exists:users,id',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:USD,CDF',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,mobile_money,bank,autre',
            'reference' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // Upload du nouveau justificatif
            if ($request->hasFile('receipt_image')) {
                // Supprimer l'ancien
                if ($expense->receipt_image && Storage::disk('public')->exists('expenses/' . $expense->receipt_image)) {
                    Storage::disk('public')->delete('expenses/' . $expense->receipt_image);
                }

                $file = $request->file('receipt_image');
                $filename = 'expense_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('expenses', $filename, 'public');
                $validated['receipt_image'] = $filename;
            }

            $expense->update($validated);

            DB::commit();

            return redirect()
                ->route('cashier.expenses.index')
                ->with('success', 'Dépense modifiée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification dépense: ' . $e->getMessage());

            return back()
                ->with('error', 'Erreur : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une dépense (soft delete)
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);

        // Un caissier ne peut supprimer que ses propres dépenses
        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            if ($expense->user_id !== auth()->id()) {
                abort(403);
            }
        }

        try {
            $expense->delete(); // Soft delete

            return redirect()
                ->route('cashier.expenses.index')
                ->with('success', 'Dépense supprimée avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur suppression dépense: ' . $e->getMessage());
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Export CSV
     */
    public function export(Request $request)
    {
        $query = Expense::with(['user', 'relatedUser'])->approved();

        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="depenses_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($file, [
                'ID', 'Date', 'Type', 'Catégorie', 'Titre', 'Description',
                'Montant', 'Devise', 'Paiement', 'Référence',
                'Enregistré par', 'Membre lié'
            ]);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->id,
                    $expense->expense_date->format('d/m/Y'),
                    $expense->expense_type_label,
                    $expense->category_label,
                    $expense->title,
                    $expense->description,
                    number_format($expense->amount, 2),
                    $expense->currency,
                    $expense->payment_method_label,
                    $expense->reference,
                    $expense->user->name ?? 'N/A',
                    $expense->relatedUser->name ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Statistiques détaillées
     */
    public function stats(Request $request)
    {
        $baseQuery = Expense::approved();

        if (auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
            $baseQuery->where('user_id', auth()->id());
        }

        // Par mois (12 derniers)
        $monthlyData = (clone $baseQuery)
            ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as month, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Par catégorie
        $byCategory = (clone $baseQuery)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        // Par devise
        $byCurrency = (clone $baseQuery)
            ->selectRaw('currency, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('currency')
            ->get();

        // Top 10 dépenses
        $topExpenses = (clone $baseQuery)
            ->orderBy('amount', 'desc')
            ->limit(10)
            ->with('user')
            ->get();

        $stats = [
            'total_year' => (clone $baseQuery)->thisYear()->sum('amount'),
            'total_month' => (clone $baseQuery)->thisMonth()->sum('amount'),
            'total_today' => (clone $baseQuery)->today()->sum('amount'),
            'count_year' => (clone $baseQuery)->thisYear()->count(),
            'count_month' => (clone $baseQuery)->thisMonth()->count(),
            'average' => (clone $baseQuery)->thisYear()->avg('amount') ?? 0,
        ];

        return view('cashier.expenses.stats', compact(
            'stats', 'monthlyData', 'byCategory', 'byCurrency', 'topExpenses'
        ));
    }

    /**
     * API : Rechercher des membres (AJAX)
     */
    public function searchMembers(Request $request)
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $members = User::where('is_active', true)
            ->whereIn('user_type', ['member', 'client'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('phone', 'LIKE', "%{$q}%")
                      ->orWhere('sponsor_id', 'LIKE', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'phone', 'sponsor_id', 'user_type']);

        return response()->json($members);
    }
}