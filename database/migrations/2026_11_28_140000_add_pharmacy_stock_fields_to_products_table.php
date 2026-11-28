<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->nullable();
            }
            if (! Schema::hasColumn('products', 'packaging')) {
                $table->string('packaging')->nullable();
            }
            if (! Schema::hasColumn('products', 'dosage')) {
                $table->string('dosage')->nullable();
            }
            if (! Schema::hasColumn('products', 'min_stock')) {
                $table->unsignedInteger('min_stock')->default(10)->after('stock');
            }
            if (! Schema::hasColumn('products', 'expires_at')) {
                $table->date('expires_at')->nullable()->after('min_stock');
            }
            if (! Schema::hasColumn('products', 'ean')) {
                $table->string('ean', 32)->nullable()->index()->after('sku');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $drop = [];
            foreach (['min_stock', 'expires_at', 'ean'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
