<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sponsor_id')->nullable()->after('email');
            $table->string('phone')->nullable()->after('sponsor_id');
            $table->string('rank')->default('Distributor')->after('phone');
            $table->unsignedBigInteger('rank_id')->nullable()->after('rank');
            $table->unsignedBigInteger('package_id')->nullable()->after('rank_id');
            $table->integer('pv_balance')->default(0)->after('package_id');
            $table->integer('bv_balance')->default(0)->after('pv_balance');
            $table->decimal('commission_balance', 15, 2)->default(0)->after('bv_balance');
            $table->decimal('total_earnings', 15, 2)->default(0)->after('commission_balance');
            $table->integer('total_sponsors')->default(0)->after('total_earnings');
            $table->integer('total_team')->default(0)->after('total_sponsors');
            $table->boolean('is_active')->default(true)->after('total_team');
            $table->timestamp('package_expiry')->nullable()->after('is_active');
            $table->string('avatar')->nullable()->after('package_expiry');
            $table->string('country')->nullable()->after('avatar');
            $table->string('city')->nullable()->after('country');
            $table->text('address')->nullable()->after('city');

            $table->foreign('rank_id')->references('id')->on('ranks')->onDelete('set null');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rank_id']);
            $table->dropForeign(['package_id']);
            $table->dropColumn(['sponsor_id', 'phone', 'rank', 'rank_id', 'package_id', 'pv_balance', 'bv_balance', 'commission_balance', 'total_earnings', 'total_sponsors', 'total_team', 'is_active', 'package_expiry', 'avatar', 'country', 'city', 'address']);
        });
    }
};
