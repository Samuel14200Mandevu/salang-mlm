<?php

namespace App\Http\Controllers;

use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $documents = KycDocument::where('user_id', $user->id)->get();

        return view('kyc.index', compact('user', 'documents'));
    }

    public function create()
    {
        return view('kyc.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:id_card,passport,proof_of_address,selfie',
            'document_number' => 'nullable|string|max:255',
            'file' => 'required|file|max:5120|mimes:jpeg,png,jpg,pdf',
        ]);

        $user = Auth::user();

        $existing = KycDocument::where('user_id', $user->id)
            ->where('document_type', $request->document_type)
            ->whereIn('status', ['pending', 'verified'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Vous avez déjà soumis ce type de document.');
        }

        $file = $request->file('file');
        $filename = time() . '_' . $user->id . '_' . $request->document_type . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('kyc', $filename, 'public');

        KycDocument::create([
            'user_id' => $user->id,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
        ]);

        $user->kyc_status = 'pending';
        $user->save();

        return redirect()->route('kyc.index')
            ->with('success', 'Document soumis avec succès. En attente de vérification.');
    }

    public function adminIndex()
    {
        $pendingDocs = KycDocument::where('status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('admin.kyc.index', compact('pendingDocs'));
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

    public function getStatus()
    {
        $user = Auth::user();

        return response()->json([
            'kyc_status' => $user->kyc_status,
            'is_verified' => $user->kyc_status === 'verified',
        ]);
    }
}