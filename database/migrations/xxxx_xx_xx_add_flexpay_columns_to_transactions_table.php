<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            $table->foreignId('order_id')
                ->nullable()
                ->after('wallet_id')
                ->constrained()
                ->onDelete('set null');
            
            $table->string('transaction_id')
                ->nullable()
                ->after('reference');
            
            $table->string('provider')
                ->nullable()
                ->after('transaction_id');
            
            // Ajouter des index pour les performances
            $table->index(['transaction_id']);
            $table->index(['provider']);
            $table->index(['order_id']);
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn(['order_id', 'transaction_id', 'provider']);
        });
    }
};