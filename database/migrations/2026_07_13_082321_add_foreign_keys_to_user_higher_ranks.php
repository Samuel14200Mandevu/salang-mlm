<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_higher_ranks', function (Blueprint $table) {
            $table->foreign('higher_rank_id')
                  ->references('id')
                  ->on('higher_ranks')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('user_higher_ranks', function (Blueprint $table) {
            $table->dropForeign(['higher_rank_id']);
        });
    }
};
