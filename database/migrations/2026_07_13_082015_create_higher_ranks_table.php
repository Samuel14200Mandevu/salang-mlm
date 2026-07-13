<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('higher_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Rubis, Saphir, Diamant 1, ...');
            $table->string('slug')->unique();
            $table->integer('level')->comment("Pour l'ordre: 1=Rubis, 2=Saphir, etc.");
            $table->integer('min_branches_rank_9')->default(0)->comment('Nombre de branches Niveau 9 exigé');
            $table->integer('min_branches_diamond')->nullable()->comment('Pour Actionnaire: branches Diamant exigées');
            $table->decimal('global_bonus_percentage', 5, 2)->default(0.00)->comment('Part du bonus mondial (ex: 2.5 pour Rubis)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('higher_ranks');
    }
};
