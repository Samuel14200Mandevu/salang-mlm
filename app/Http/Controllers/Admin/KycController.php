<?php
// app/Http/Controllers/Admin/KycController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycController extends Controller
{
    private function getRequiredKycDocuments(): array
    {
        return config('kyc.required_documents', ['id_card', 'proof_of_address']);
    }

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
            return back()->with('error', 'This document has already been processed.');
        }

        $document->status = $request->status;
        $document->rejection_reason = $request->rejection_reason;
        $document->verified_by = Auth::id();
        $document->verified_at = now();
        $document->save();

        $user = $document->user;
        $requiredDocs = $this->getRequiredKycDocuments();

        if ($request->status === 'verified') {
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

        return back()->with('success', 'Document processed successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $document = KycDocument::findOrFail($id);
        $document->status = 'rejected';
        $document->rejection_reason = $request->reason;
        $document->verified_by = Auth::id();
        $document->verified_at = now();
        $document->save();

        $user = $document->user;
        $user->kyc_status = 'rejected';
        $user->save();

        return back()->with('success', 'Document rejected.');
    }
}