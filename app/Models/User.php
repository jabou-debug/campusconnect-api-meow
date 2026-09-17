<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'student_id', 'role', 'profile_photo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function announcements() { 
    return $this->hasMany(Announcement::class); 
    } 
    
    public function organizedEvents() { 
        return $this->hasMany(Event::class); 
    } 
    
    public function rsvpedEvents() { 
        return $this->belongsToMany(Event::class, "event_rsvps") 
                    ->withPivot("status","rsvp_at") 
                    ->withTimestamps(); 
    } 
    
    public function schedules() { 
        return $this->hasMany(Schedule::class); 
    } 
    
    public function notifications() { 
        return $this->hasMany(Notification::class); 
    } 
    
    public function chatbotMessages() { 
        return $this->hasMany(ChatbotMessage::class); 
    }
}
