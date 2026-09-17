<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model { 
    use HasFactory; 
 
    protected $fillable = [ 
        "user_id","title","description","location","date", 
        "time_start","time_end","category","status","capacity", 
        "is_free","contact_email","image_url","registration_deadline", 
    ]; 
 
    protected $casts = [ 
        "date"                  => "date", 
        "registration_deadline" => "date", 
        "is_free"               => "boolean", 
        "capacity"              => "integer", 
    ]; 
 
    // The user who organizes this event 
    public function organizer() { 
        return $this->belongsTo(User::class, "user_id"); 
    } 
 
    // All RSVP records for this event 
    public function rsvps() { 
        return $this->hasMany(EventRsvp::class); 
    } 
 
    // All users who have RSVPed (many-to-many via event_rsvps) 
    public function attendees() { 
        return $this->belongsToMany(User::class, "event_rsvps") 
                    ->withPivot("status","rsvp_at") 
                    ->withTimestamps(); 
    } 
 
    // Computed: available seats 
    public function getSeatsAvailableAttribute(): int { 
        return max(0, $this->capacity - $this->rsvps()->count()); 
    } 
 
    // Computed: is fully booked? 
    public function getIsFullAttribute(): bool { 
        return $this->rsvps()->count() >= $this->capacity; 
    }
    public function scopeUpcoming($query) { 
        return $query->where("status","upcoming") 
                     ->where("date",">=",now()->toDateString()); 
    } 
} 
