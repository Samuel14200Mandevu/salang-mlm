<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_sheets', function (Blueprint $table) {
            $table->id();
            $table->date('activity_date')->unique();
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activity_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_sheet_id')->constrained('activity_sheets')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('users')->cascadeOnDelete();
            $table->string('member_code', 32)->nullable()->index();
            $table->string('participant_name');
            $table->string('participant_phone', 40)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(1);

            // Invité / motif du jour
            $table->string('invite_type', 40)->default('formation'); // formation, autre
            $table->boolean('formation_done')->default(true);

            // Étapes suivantes
            $table->boolean('adhesion_done')->default(false);
            $table->foreignId('adhesion_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('adhesion_at')->nullable();

            $table->boolean('consultation_done')->default(false);
            // Pas de FK : la table consultations peut être absente en tests SQLite
            $table->unsignedBigInteger('consultation_id')->nullable()->index();
            $table->timestamp('consultation_at')->nullable();

            $table->boolean('purchase_done')->default(false);
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('purchases_amount', 12, 2)->nullable();
            $table->decimal('net_sales_amount', 12, 2)->nullable();
            $table->decimal('validated_amount', 12, 2)->nullable();
            $table->timestamp('purchase_at')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['activity_sheet_id', 'member_id']);
            $table->index(['participant_name', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_entries');
        Schema::dropIfExists('activity_sheets');
    }
};
