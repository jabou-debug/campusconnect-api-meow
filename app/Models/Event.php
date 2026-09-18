<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'description', 'location', 'date',
        'time_start', 'time_end', 'category', 'status', 'capacity',
        'is_free', 'contact_email', 'image_url', 'registration_deadline',
    ];

    protected $casts = [
        'date'                  => 'date',
        'registration_deadline' => 'date',
        'is_free'               => 'boolean',
        'capacity'              => 'integer',
    ];

    // ── Relationships ───────────────────────────────────────────────

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rsvps()
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'event_rsvps')
                    ->withPivot('status', 'rsvp_at')
                    ->withTimestamps();
    }

    // ── Computed Accessors ──────────────────────────────────────────

    /** Total confirmed RSVPs */
    public function getRsvpCountAttribute(): int
    {
        return $this->rsvps()->count();
    }

    /** Remaining seats */
    public function getSeatsAvailableAttribute(): int
    {
        return max(0, $this->capacity - $this->rsvps()->count());
    }

    /** Is the event fully booked? */
    public function getIsFullAttribute(): bool
    {
        return $this->rsvps()->count() >= $this->capacity;
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
                     ->where('date', '>=', now()->toDateString());
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }
}