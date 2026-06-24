<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('from_user_id')->nullable();
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('set null');
            $table->index(['user_id', 'status']);
            $table->index(['type', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('commissions'); }
};
