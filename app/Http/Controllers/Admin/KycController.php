<?php
// app/Http/Controllers/Admin/KycController.php - Version Admin

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycController extends Controller
{
    public function index()
    {
        $pendingDocs = KycDocument::where('status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        $stats = [
            'pending' => KycDocument::where('status', 'pending')->count(),
            'verified' => KycDocument::where('status', 'verified')->count(),
            'rejected' => KycDocument::where('status', 'rejected')->count(),
            'total' => KycDocument::count(),
        ];

        return view('admin.kyc.index', compact('pendingDocs', 'stats'));
    }

    public function verify(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|nullable',
        ]);

        $document = KycDocument::findOrFail($id);

        if ($document->status !== 'pending') {
            return back()->with('error', 'Ce document a déjà été traité.');
        }

        $document->status = $request->status;
        $document->rejection_reason = $request->rejection_reason;
        $document->verified_by = Auth::id();
        $document->verified_at = now();
        $document->save();

        $user = $document->user;
        
        if ($request->status === 'verified') {
            $requiredDocs = ['id_card', 'proof_of_address'];
            $verifiedDocs = KycDocument::where('user_id', $user->id)
                ->where('status', 'verified')
                ->whereIn('document_type', $requiredDocs)
                ->count();

            if ($verifiedDocs >= count($requiredDocs)) {
                $user->kyc_status = 'verified';
                $user->kyc_verified_at = now();
            } else {
                $user->kyc_status = 'partial';
            }
        } else {
            $user->kyc_status = 'rejected';
        }
        $user->save();

        $message = $request->status === 'verified'
            ? 'Document vérifié avec succès.'
            : 'Document rejeté.';

        return back()->with('success', $message);
    }

    public function show($id)
    {
        $document = KycDocument::with('user')->findOrFail($id);
        return view('admin.kyc.show', compact('document'));
    }
}