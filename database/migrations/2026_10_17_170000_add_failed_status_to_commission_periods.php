<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE commission_periods MODIFY COLUMN status ENUM(
                'pending', 'calculating', 'calculated', 'paying', 'paid', 'closed', 'failed'
            ) NOT NULL DEFAULT 'pending'");

            return;
        }

        if (DB::getDriverName() === 'sqlite' && Schema::hasTable('commission_periods')) {
            $this->rebuildSqliteCommissionPeriodsWithoutStatusCheck();
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE commission_periods MODIFY COLUMN status ENUM(
                'pending', 'calculating', 'calculated', 'paying', 'paid', 'closed'
            ) NOT NULL DEFAULT 'pending'");
        }
    }

    private function rebuildSqliteCommissionPeriodsWithoutStatusCheck(): void
    {
        DB::statement('PRAGMA foreign_keys=off');

        Schema::create('commission_periods_new', function (Blueprint $table) {
            $table->id();
            $table->string('period', 10)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('calculation_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('status', 32)->default('pending');
            $table->decimal('total_commissions', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_historical')->default(false);
            $table->timestamp('paid_offline_at')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });

        DB::statement('INSERT INTO commission_periods_new (
            id, period, start_date, end_date, calculation_date, payment_date, status,
            total_commissions, total_paid, notes, is_historical, paid_offline_at, is_hidden,
            created_at, updated_at
        ) SELECT
            id, period, start_date, end_date, calculation_date, payment_date, status,
            total_commissions, total_paid, notes,
            COALESCE(is_historical, 0), paid_offline_at, COALESCE(is_hidden, 0),
            created_at, updated_at
        FROM commission_periods');

        Schema::drop('commission_periods');
        Schema::rename('commission_periods_new', 'commission_periods');

        DB::statement('PRAGMA foreign_keys=on');
    }
};
