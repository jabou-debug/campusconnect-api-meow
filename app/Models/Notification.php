<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/Notification.php 
class Notification extends Model { 
    use HasFactory; 
    protected $table = "notifications"; // explicit — avoids Laravel conflicts 
    protected $fillable = [ 
        "user_id","title","message","type","icon", 
        "is_read","action_url","read_at", 
    ]; 
    protected $casts = ["is_read" => "boolean", "read_at" => "datetime"]; 
    public function user() { return $this->belongsTo(User::class); } 
    public function scopeUnread($q) { return $q->where("is_read", false); } 
    public function scopeByType($q, string $t) { return $q->where("type",$t); } 
    public function markAsRead(): void { 
        $this->update(["is_read" => true, "read_at" => now()]); 
    } 
} 
