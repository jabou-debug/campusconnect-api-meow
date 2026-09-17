<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


// app/Models/ChatbotMessage.php 
class ChatbotMessage extends Model { 
    use HasFactory; 
    protected $fillable = 
["user_id","message","response","conversation_id","tokens_used"]; 
    protected $casts = ["tokens_used" => "integer"]; 
    public function user() { return $this->belongsTo(User::class); } 
}
