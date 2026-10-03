<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'parrain_id')) {
                $table->unsignedBigInteger('parrain_id')->nullable()->after('sponsor_id');
                $table->foreign('parrain_id')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'position')) {
                $table->string('position', 10)->nullable()->after('parrain_id');
            }
            if (!Schema::hasColumn('users', 'monthly_pv')) {
                $table->decimal('monthly_pv', 15, 2)->default(0)->after('bv_balance');
            }
            if (!Schema::hasColumn('users', 'monthly_bv')) {
                $table->decimal('monthly_bv', 15, 2)->default(0)->after('monthly_pv');
            }
            if (!Schema::hasColumn('users', 'team_pv')) {
                $table->decimal('team_pv', 15, 2)->default(0)->after('monthly_bv');
            }
            if (!Schema::hasColumn('users', 'team_bv')) {
                $table->decimal('team_bv', 15, 2)->default(0)->after('team_pv');
            }
            if (!Schema::hasColumn('users', 'rank_level')) {
                $table->unsignedTinyInteger('rank_level')->default(1)->after('rank_id');
            }
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type', 20)->default('member')->after('is_active');
            }
            if (!Schema::hasColumn('users', 'qualified_branches')) {
                $table->unsignedInteger('qualified_branches')->default(0)->after('total_team');
            }
            if (!Schema::hasColumn('users', 'direct_sponsors_count')) {
                $table->unsignedInteger('direct_sponsors_count')->default(0)->after('qualified_branches');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'parrain_id')) {
                $table->dropForeign(['parrain_id']);
            }
            foreach ([
                'parrain_id', 'position', 'monthly_pv', 'monthly_bv', 'team_pv', 'team_bv',
                'rank_level', 'user_type', 'qualified_branches', 'direct_sponsors_count',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
