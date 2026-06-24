<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('genealogy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('level')->default(0);
            $table->string('position')->nullable();
            $table->integer('left_count')->default(0);
            $table->integer('right_count')->default(0);
            $table->integer('total_children')->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sponsor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['sponsor_id', 'level']);
            $table->unique(['user_id', 'sponsor_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('genealogy'); }
};
