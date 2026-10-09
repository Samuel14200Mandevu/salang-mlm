<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'question_conversation_id',
        'user_id',
        'subject',
        'body',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(QuestionConversation::class, 'question_conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isFromMember(QuestionConversation $conversation): bool
    {
        return $this->user_id === $conversation->user_id;
    }
}
