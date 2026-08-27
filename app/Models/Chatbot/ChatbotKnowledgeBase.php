<?php

namespace App\Models\Chatbot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotKnowledgeBase extends Model
{
    use HasFactory;

    protected $table = 'chatbot_knowledge_bases';

    protected $fillable = [
        'intent',
        'question_pattern',
        'answer',
        'entities',
        'status',
    ];

    protected $casts = [
        'entities' => 'array',
    ];
}
