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
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('min_pv')->default(0);
            $table->integer('min_bv')->default(0);
            $table->integer('min_sponsors')->default(0);
            $table->integer('min_team')->default(0);
            $table->decimal('bonus_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insérer les rangs par défaut
        $ranks = [
            ['name' => 'Distributor', 'slug' => 'distributor', 'min_pv' => 0, 'bonus_percentage' => 0],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'min_pv' => 100, 'bonus_percentage' => 5],
            ['name' => 'Assistant Manager', 'slug' => 'assistant-manager', 'min_pv' => 200, 'bonus_percentage' => 10],
            ['name' => 'Manager', 'slug' => 'manager', 'min_pv' => 500, 'bonus_percentage' => 15],
            ['name' => 'Senior Manager', 'slug' => 'senior-manager', 'min_pv' => 1000, 'bonus_percentage' => 20],
            ['name' => 'Soaring Manager', 'slug' => 'soaring-manager', 'min_pv' => 2000, 'bonus_percentage' => 25],
            ['name' => 'Sapphire Manager', 'slug' => 'sapphire-manager', 'min_pv' => 5000, 'bonus_percentage' => 30],
            ['name' => 'Blue Diamond', 'slug' => 'blue-diamond', 'min_pv' => 10000, 'bonus_percentage' => 35],
            ['name' => 'Diamond', 'slug' => 'diamond', 'min_pv' => 20000, 'bonus_percentage' => 40],
            ['name' => 'Pearl', 'slug' => 'pearl', 'min_pv' => 50000, 'bonus_percentage' => 50],
        ];

        foreach ($ranks as $rank) {
            \DB::table('ranks')->insert($rank);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
