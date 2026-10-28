<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\MLM\MemberDownlinePeriodReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    use GeneratesPdfSafely;

    public function index()
    {
        $user = Auth::user();

        $totalEarnings = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        $totalWithdrawn = Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        $transactionsCount = Transaction::where('user_id', $user->id)->count();
        $ordersCount = Order::where('user_id', $user->id)->count();

        $wallet = $user->wallet;
        $balance = $wallet ? (float) $wallet->balance : 0.0;

        $commissionsCount = Commission::where('user_id', $user->id)->count();

        return view('report.index', compact(
            'totalEarnings',
            'totalWithdrawn',
            'transactionsCount',
            'ordersCount',
            'balance',
            'commissionsCount'
        ));
    }

    public function earnings()
    {
        return redirect()->route('commissions.index');
    }

    public function network()
    {
        return redirect()->route('network.index');
    }

    public function export(Request $request)
    {
        return redirect()->route('wallet.export', $request->query());
    }

    /**
     * Liste des périodes (depuis janv. 2026) pour le rapport détaillé filleuls.
     */
    public function downline(MemberDownlinePeriodReport $report)
    {
        $periods = $report->availablePeriods();

        return view('report.downline.index', [
            'periods' => $periods,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Visionneuse PDF : barre Retour + Télécharger, aperçu du rapport.
     */
    public function downlineShow(string $period, MemberDownlinePeriodReport $report)
    {
        if (! $report->isValidPeriod($period)) {
            return redirect()
                ->route('report.downline')
                ->with('error', 'Période invalide.');
        }

        $user = Auth::user()->loadMissing(['parrain:id,name,sponsor_id']);
        $rows = $report->buildRows($user, $period);
        $totalMonthlyPv = array_sum(array_column($rows, 'monthly_pv'));
        $memberStats = $report->buildMemberStats($user, $period, $totalMonthlyPv);

        return view('report.downline.show', [
            'period' => $period,
            'periodLabel' => $report->periodLabel($period),
            'user' => $user,
            'rows' => $rows,
            'rowsCount' => count($rows),
            'totalMonthlyPv' => $totalMonthlyPv,
            'memberStats' => $memberStats,
            'pdfDownloadUrl' => route('report.downline.pdf', ['period' => $period, 'download' => 1]),
            'date_generated' => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * PDF du rapport : inline (aperçu) ou téléchargement (?download=1).
     */
    public function downlinePdf(string $period, Request $request, MemberDownlinePeriodReport $report)
    {
        if (! $report->isValidPeriod($period)) {
            return redirect()
                ->route('report.downline')
                ->with('error', 'Période invalide.');
        }

        try {
            $user = Auth::user()->loadMissing(['parrain:id,name,sponsor_id']);
            $rows = $report->buildRows($user, $period);
            $totalMonthlyPv = array_sum(array_column($rows, 'monthly_pv'));
            $memberStats = $report->buildMemberStats($user, $period, $totalMonthlyPv);
            $filename = 'rapport_filleuls_'.$period.'_'.$user->sponsor_id.'.pdf';
            $data = [
                'period' => $period,
                'periodLabel' => $report->periodLabel($period),
                'rows' => $rows,
                'user' => $user,
                'rowsCount' => count($rows),
                'totalMonthlyPv' => $totalMonthlyPv,
                'memberStats' => $memberStats,
                'logoBase64' => $this->loadPdfLogoBase64(),
                'date_generated' => now()->format('d/m/Y H:i'),
            ];

            if ($request->boolean('download')) {
                return $this->downloadPdfView(
                    'report.downline.pdf',
                    $data,
                    $filename,
                    'a4',
                    'landscape'
                );
            }

            return $this->streamPdfView(
                'report.downline.pdf',
                $data,
                $filename,
                'a4',
                'landscape'
            );
        } catch (\Throwable $e) {
            $this->logPdfFailure('report.downlinePdf', $e);

            return redirect()
                ->route('report.downline')
                ->with('error', 'Impossible de générer le PDF. Réessayez.');
        }
    }
}
