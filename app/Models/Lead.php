<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'company',
        'email',
        'phone',
        'mobile',
        'city',
        'country',
        'added_by',
        'status',
        'value',
        'product',
        'expected_close_date',
        'actual_close_date',
        'lost_reason',
        'won_at',
        'is_client',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'won_at' => 'datetime',
        'is_client' => 'boolean',
    ];

    /**
     * Get the user who added this lead.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get all activities for this lead.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get all tasks for this lead.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get all follow-up schedules for this lead.
     */
    public function followUpSchedules(): HasMany
    {
        return $this->hasMany(FollowUpSchedule::class);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include won leads.
     */
    public function scopeWon(Builder $query): Builder
    {
        return $query->where('status', 'won');
    }

    /**
     * Scope a query to only include lost leads.
     */
    public function scopeLost(Builder $query): Builder
    {
        return $query->where('status', 'lost');
    }

    /**
     * Scope a query to only include active leads (not won or lost).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['won', 'lost']);
    }

    /**
     * Scope a query to only include clients (leads where is_client = true).
     */
    public function scopeClients(Builder $query): Builder
    {
        return $query->where('is_client', true);
    }

    /**
     * Scope a query to only include leads (where is_client = false).
     */
    public function scopeLeads(Builder $query): Builder
    {
        return $query->where('is_client', false);
    }

    /**
     * Scope a query to only include new leads.
     */
    public function scopeNewLeads(Builder $query): Builder
    {
        return $query->where('status', 'new_lead');
    }

    /**
     * Mark the lead as won.
     */
    public function markAsWon(): void
    {
        $this->update([
            'status' => 'won',
            'is_client' => true,
            'won_at' => now(),
            'actual_close_date' => now(),
        ]);
    }

    /**
     * Mark the lead as lost.
     */
    public function markAsLost(string $reason): void
    {
        $this->update([
            'status' => 'lost',
            'lost_reason' => $reason,
            'actual_close_date' => now(),
        ]);
    }
}
