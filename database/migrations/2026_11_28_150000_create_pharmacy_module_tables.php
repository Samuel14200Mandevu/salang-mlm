<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pharmacy_insurers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('coverage_rate', 5, 4)->default(0); // 0.80 = 80%
            $table->string('contact')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pharmacy_patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->foreignId('insurer_id')->nullable()->constrained('pharmacy_insurers')->nullOnDelete();
            $table->string('member_number')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pharmacy_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('purchased_at');
            $table->string('invoice_number')->nullable();
            $table->foreignId('supplier_id')->constrained('pharmacy_suppliers')->cascadeOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('unpaid'); // paid|partial|unpaid
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['purchased_at', 'status']);
        });

        Schema::create('pharmacy_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('pharmacy_purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();
        });

        Schema::create('pharmacy_sales', function (Blueprint $table) {
            $table->id();
            $table->date('sold_at');
            $table->string('ticket_number')->unique();
            $table->foreignId('patient_id')->nullable()->constrained('pharmacy_patients')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('payment_method', 40)->default('cash'); // cash|mobile_money|card|insurance
            $table->foreignId('insurer_id')->nullable()->constrained('pharmacy_insurers')->nullOnDelete();
            $table->decimal('coverage_rate', 5, 4)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('insurance_amount', 15, 2)->default(0);
            $table->decimal('patient_amount', 15, 2)->default(0);
            $table->decimal('cost_amount', 15, 2)->default(0);
            $table->decimal('profit_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['sold_at', 'payment_method']);
        });

        Schema::create('pharmacy_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('pharmacy_sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->decimal('insurance_share', 15, 2)->default(0);
            $table->decimal('patient_share', 15, 2)->default(0);
            $table->decimal('cost_total', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('pharmacy_cash_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('label');
            $table->string('category', 40)->default('other'); // sales_receipt|expense|bank_deposit|other
            $table->decimal('amount_in', 15, 2)->default(0);
            $table->decimal('amount_out', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('entry_date');
        });

        Schema::create('pharmacy_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('cash_float', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_settings');
        Schema::dropIfExists('pharmacy_cash_entries');
        Schema::dropIfExists('pharmacy_sale_items');
        Schema::dropIfExists('pharmacy_sales');
        Schema::dropIfExists('pharmacy_purchase_items');
        Schema::dropIfExists('pharmacy_purchases');
        Schema::dropIfExists('pharmacy_patients');
        Schema::dropIfExists('pharmacy_insurers');
        Schema::dropIfExists('pharmacy_suppliers');
    }
};
