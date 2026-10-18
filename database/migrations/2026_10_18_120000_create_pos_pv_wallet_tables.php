<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_pv_balances')) {
            Schema::create('user_pv_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('total_pv')->default(0);
                $table->unsignedBigInteger('allocated_pv')->default(0);
                $table->unsignedBigInteger('pending_pv')->default(0);
                $table->unsignedBigInteger('available_pv')->default(0);
                $table->unsignedBigInteger('total_bv')->default(0);
                $table->unsignedBigInteger('allocated_bv')->default(0);
                $table->unsignedBigInteger('pending_bv')->default(0);
                $table->unsignedBigInteger('available_bv')->default(0);
                $table->timestamp('last_updated_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pv_allocations')) {
            Schema::create('pv_allocations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('distributor_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('pv_amount');
                $table->unsignedInteger('bv_amount')->default(0);
                $table->string('distribution_type', 32)->default('manual');
                $table->text('notes')->nullable();
                $table->string('status', 32)->default('pending');
                $table->timestamp('allocated_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('period', 7)->nullable();
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pv_transactions')) {
            Schema::create('pv_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('pv_allocation_id')->nullable();
                $table->string('type', 16);
                $table->string('source', 32);
                $table->integer('pv_amount')->default(0);
                $table->integer('bv_amount')->default(0);
                $table->decimal('cash_amount', 12, 2)->nullable();
                $table->string('description')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pv_transactions');
        Schema::dropIfExists('pv_allocations');
        Schema::dropIfExists('user_pv_balances');
    }
};
