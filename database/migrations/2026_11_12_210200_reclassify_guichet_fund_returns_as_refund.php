<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Retours de fonds (annulation guichet) ne sont pas des dépôts.
     */
    public function up(): void
    {
        DB::table('transactions')
            ->where('type', 'deposit')
            ->where(function ($query) {
                $query->where('description', 'like', 'Retour de fonds%')
                    ->orWhere('description', 'like', 'Recréditation retrait guichet%');
            })
            ->update(['type' => 'refund']);
    }

    public function down(): void
    {
        DB::table('transactions')
            ->where('type', 'refund')
            ->where(function ($query) {
                $query->where('description', 'like', 'Retour de fonds%')
                    ->orWhere('description', 'like', 'Recréditation retrait guichet%');
            })
            ->update(['type' => 'deposit']);
    }
};
