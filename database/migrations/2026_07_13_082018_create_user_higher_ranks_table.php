<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_higher_ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('higher_rank_id');
            $table->timestamp('achieved_at')->useCurrent();
            $table->string('period', 10);
            $table->timestamps();
            
            $table->unique(['user_id', 'higher_rank_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_higher_ranks');
    }
};
