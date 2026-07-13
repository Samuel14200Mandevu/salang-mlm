<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'facebook_id')) {
                $table->string('facebook_id')->nullable()->after('google_id');
            }
            if (!Schema::hasColumn('users', 'twitter_id')) {
                $table->string('twitter_id')->nullable()->after('facebook_id');
            }
            if (!Schema::hasColumn('users', 'instagram_id')) {
                $table->string('instagram_id')->nullable()->after('twitter_id');
            }
            if (!Schema::hasColumn('users', 'tiktok_id')) {
                $table->string('tiktok_id')->nullable()->after('instagram_id');
            }
            if (!Schema::hasColumn('users', 'last_provider')) {
                $table->string('last_provider', 50)->nullable()->after('tiktok_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['google_id', 'facebook_id', 'twitter_id', 'instagram_id', 'tiktok_id', 'last_provider'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
