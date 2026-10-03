<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_periods', function (Blueprint $table) {
            $table->boolean('is_historical')->default(false)->after('status');
            $table->timestamp('paid_offline_at')->nullable()->after('is_historical');
            $table->boolean('is_hidden')->default(false)->after('paid_offline_at');

            $table->index('is_historical');
            $table->index('is_hidden');
        });
    }

    public function down(): void
    {
        Schema::table('commission_periods', function (Blueprint $table) {
            $table->dropIndex(['is_historical']);
            $table->dropIndex(['is_hidden']);
            $table->dropColumn(['is_historical', 'paid_offline_at', 'is_hidden']);
        });
    }
};
