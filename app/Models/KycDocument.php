<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => '⏳ En attente',
            'verified' => '✅ Vérifié',
            'rejected' => '❌ Rejeté',
            'not_submitted' => '📤 Non soumis',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getDocumentTypeLabelAttribute()
    {
        $labels = [
            'id_card' => 'Carte d\'identité',
            'passport' => 'Passeport',
            'proof_of_address' => 'Justificatif de domicile',
            'selfie' => 'Selfie avec pièce d\'identité',
        ];
        return $labels[$this->document_type] ?? ucfirst($this->document_type);
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/kyc/' . $this->file_path);
    }
}