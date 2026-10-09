<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('last_message_at');
        });

        Schema::create('question_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_conversation_id')->constrained('question_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject', 255)->nullable();
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['question_conversation_id', 'created_at']);
        });

        if (Schema::hasTable('questions')) {
            $this->migrateLegacyQuestions();
            Schema::drop('questions');
        }
    }

    public function down(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject', 255);
            $table->text('body');
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });

        Schema::dropIfExists('question_messages');
        Schema::dropIfExists('question_conversations');
    }

    protected function migrateLegacyQuestions(): void
    {
        $rows = DB::table('questions')->orderBy('created_at')->get();

        if ($rows->isEmpty()) {
            return;
        }

        $conversationIds = [];

        foreach ($rows->groupBy('user_id') as $userId => $questions) {
            $last = $questions->last();
            $conversationId = DB::table('question_conversations')->insertGetId([
                'user_id' => $userId,
                'status' => $last->status,
                'last_message_at' => $last->answered_at ?? $last->created_at,
                'created_at' => $questions->first()->created_at,
                'updated_at' => $last->updated_at,
            ]);

            $conversationIds[$userId] = $conversationId;

            foreach ($questions as $question) {
                DB::table('question_messages')->insert([
                    'question_conversation_id' => $conversationId,
                    'user_id' => $question->user_id,
                    'subject' => $question->subject,
                    'body' => $question->body,
                    'created_at' => $question->created_at,
                ]);

                if ($question->answer) {
                    DB::table('question_messages')->insert([
                        'question_conversation_id' => $conversationId,
                        'user_id' => $question->answered_by ?? $question->user_id,
                        'subject' => null,
                        'body' => $question->answer,
                        'created_at' => $question->answered_at ?? $question->updated_at,
                    ]);
                }
            }
        }
    }
};
