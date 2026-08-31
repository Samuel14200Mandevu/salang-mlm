<?php
// app/Models/Consultation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_id',
        'admin_id',
        'client_id',
        'code_id',
        'numero',
        'nom_complet',
        'genre',
        'age',
        'poids',
        'taille',
        'date_examen',
        'reason',
        'symptoms',
        'observations',
        'recommended_products',
        'seances_ceragem',
        'prix_ceragem',
        'seances_detox',
        'prix_detox',
        'total_produits',
        'total_services',
        'total_general',
        'admin_notes',
        'status',
        'attachment',
    ];

    protected $casts = [
        'recommended_products' => 'array',
        'date_examen' => 'date',
        'poids' => 'decimal:2',
        'taille' => 'decimal:2',
        'prix_ceragem' => 'decimal:2',
        'prix_detox' => 'decimal:2',
        'total_produits' => 'decimal:2',
        'total_services' => 'decimal:2',
        'total_general' => 'decimal:2',
    ];

    // ============================================================
    // ACCESSORS
    // ============================================================
    
    public function getGenreLabelAttribute()
    {
        return [
            'masculin' => 'Masculin',
            'feminin' => 'Féminin',
        ][$this->genre] ?? $this->genre;
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'En attente (Caissier)',
            'processing' => 'En traitement (Admin)',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
        ][$this->status] ?? 'secondary';
    }

    // ============================================================
    // RELATIONS AVEC LA TABLE USERS
    // ============================================================
    
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // ============================================================
    // MÉTHODES UTILITAIRES
    // ============================================================
    
    public function calculateTotals()
    {
        $totalProduits = 0;
        if ($this->recommended_products && is_array($this->recommended_products)) {
            foreach ($this->recommended_products as $product) {
                $totalProduits += floatval($product['prix'] ?? 0);
            }
        }
        
        $totalServices = ($this->seances_ceragem * $this->prix_ceragem) + 
                        ($this->seances_detox * $this->prix_detox);
        
        $this->total_produits = $totalProduits;
        $this->total_services = $totalServices;
        $this->total_general = $totalProduits + $totalServices;
        
        return $this;
    }

    public function isEditable()
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}