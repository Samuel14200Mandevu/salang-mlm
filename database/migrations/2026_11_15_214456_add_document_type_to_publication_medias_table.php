<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('publication_medias')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE publication_medias MODIFY COLUMN type ENUM('photo', 'video', 'document') NOT NULL");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('publication_medias')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('publication_medias')->where('type', 'document')->delete();
            DB::statement("ALTER TABLE publication_medias MODIFY COLUMN type ENUM('photo', 'video') NOT NULL");
        }
    }
};
