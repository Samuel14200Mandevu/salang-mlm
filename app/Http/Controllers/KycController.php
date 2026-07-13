<?php
// app/Http/Controllers/KycController.php

namespace App\Http\Controllers;

use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            return back()->with('error', 'You have already submitted this document type.');
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

        Log::info('KYC document submitted', [
            'user_id' => $user->id,
            'document_type' => $request->document_type,
        ]);

        return redirect()->route('kyc.index')
            ->with('success', 'Document submitted successfully. Awaiting verification.');
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