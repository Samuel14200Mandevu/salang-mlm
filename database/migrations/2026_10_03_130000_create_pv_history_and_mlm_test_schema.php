<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pv_history')) {
            Schema::create('pv_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->date('date')->nullable();
                $table->string('period', 7)->nullable();
                $table->string('type')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['user_id', 'period']);
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'source')) {
                $table->string('source', 32)->nullable()->after('payment_method');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_rank_update')) {
                $table->timestamp('last_rank_update')->nullable()->after('rank_level');
            }
        });

        Schema::table('commissions', function (Blueprint $table) {
            if (!Schema::hasColumn('commissions', 'commission_period_id')) {
                $table->unsignedBigInteger('commission_period_id')->nullable()->after('from_user_id');
            }
            if (!Schema::hasColumn('commissions', 'period')) {
                $table->string('period', 7)->nullable()->after('commission_period_id');
            }
            if (!Schema::hasColumn('commissions', 'source')) {
                $table->string('source', 32)->nullable()->after('type');
            }
            if (!Schema::hasColumn('commissions', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('package_id');
            }
            if (!Schema::hasColumn('commissions', 'generation')) {
                $table->unsignedTinyInteger('generation')->nullable();
            }
            if (!Schema::hasColumn('commissions', 'calculation_type')) {
                $table->string('calculation_type', 32)->nullable();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'activation_code')) {
                $table->string('activation_code', 64)->nullable();
            }
            if (!Schema::hasColumn('users', 'activation_code_expires_at')) {
                $table->timestamp('activation_code_expires_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'activated_at')) {
                $table->timestamp('activated_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'kyc_status')) {
                $table->string('kyc_status', 32)->default('not_submitted');
            }
        });

        if (!Schema::hasTable('commission_history')) {
            Schema::create('commission_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pv_history_id')->nullable();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('from_user_id')->nullable();
                $table->string('period', 7)->nullable();
                $table->string('type')->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->decimal('percentage', 5, 2)->nullable();
                $table->decimal('pv_used', 15, 2)->nullable();
                $table->unsignedTinyInteger('generation')->nullable();
                $table->string('status')->default('pending');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rank_histories')) {
            Schema::create('rank_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('old_rank_id')->nullable();
                $table->unsignedBigInteger('new_rank_id')->nullable();
                $table->string('old_rank_name')->nullable();
                $table->string('new_rank_name')->nullable();
                $table->decimal('pv_at_time', 15, 2)->nullable();
                $table->decimal('bv_at_time', 15, 2)->nullable();
                $table->decimal('monthly_pv_at_time', 15, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_histories');
        Schema::dropIfExists('commission_history');
        Schema::dropIfExists('pv_history');

        Schema::table('commissions', function (Blueprint $table) {
            foreach (['commission_period_id', 'period'] as $column) {
                if (Schema::hasColumn('commissions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'source')) {
                $table->dropColumn('source');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_rank_update')) {
                $table->dropColumn('last_rank_update');
            }
        });
    }
};
