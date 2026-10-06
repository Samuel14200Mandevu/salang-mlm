<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'consultation_id')) {
                $table->unsignedBigInteger('consultation_id')->nullable()->after('cashier_id');
                $table->index('consultation_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'consultation_id')) {
                $table->dropIndex(['consultation_id']);
                $table->dropColumn('consultation_id');
            }
        });
    }
};
