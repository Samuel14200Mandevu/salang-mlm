<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->default(1);
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('min_pv')->default(0);
            $table->integer('min_bv')->default(0);
            $table->integer('monthly_pv_required')->default(0);
            $table->integer('team_pv_required')->default(0);
            $table->integer('min_sponsors')->default(0);
            $table->integer('min_team')->default(0);
            $table->decimal('bonus_percentage', 5, 2)->default(0.00);
            $table->integer('pv_payment_required')->default(0);
            $table->text('description')->nullable();
            $table->json('conditions')->nullable();
            $table->json('commission_types')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
