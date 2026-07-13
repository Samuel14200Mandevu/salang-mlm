<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualified_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Le leader (ex: vous)');
            $table->foreignId('branch_user_id')->constrained('users')->onDelete('cascade')->comment('Le 1er filleul de la branche');
            $table->string('period', 10)->comment('Format: YYYY-MM');
            $table->integer('branch_rank_level')->comment('Niveau atteint par la branche (ex: 4, 5, 6)');
            $table->integer('branch_pv')->default(0)->comment('PV total de la branche sur la période');
            $table->timestamps();
            
            $table->unique(['user_id', 'branch_user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualified_branches');
    }
};
