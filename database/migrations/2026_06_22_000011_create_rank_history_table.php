<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('rank_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('old_rank_id')->nullable();
            $table->unsignedBigInteger('new_rank_id');
            $table->string('old_rank_name')->nullable();
            $table->string('new_rank_name');
            $table->integer('pv_at_time')->default(0);
            $table->integer('bv_at_time')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('old_rank_id')->references('id')->on('ranks')->onDelete('set null');
            $table->foreign('new_rank_id')->references('id')->on('ranks')->onDelete('cascade');
            $table->index(['user_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('rank_history'); }
};
