<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period', 10)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('calculation_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'calculating', 'calculated', 'paying', 'paid', 'closed'])->default('pending');
            $table->decimal('total_commissions', 15, 2)->default(0.00);
            $table->decimal('total_paid', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_periods');
    }
};
