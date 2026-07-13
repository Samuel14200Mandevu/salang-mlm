<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_monthly_ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('rank_id')->constrained()->onDelete('cascade');
            $table->string('period', 10);
            $table->integer('pv_monthly')->default(0);
            $table->integer('bv_monthly')->default(0);
            $table->integer('team_pv')->default(0);
            $table->integer('team_bv')->default(0);
            $table->integer('direct_sponsors')->default(0);
            $table->integer('qualified_branches')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_monthly_ranks');
    }
};
