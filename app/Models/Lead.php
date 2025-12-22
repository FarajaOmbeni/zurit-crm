<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_type',
        'name',
        'position',
        'company',
        'email',
        'phone',
        'city',
        'country',
        'source',
        'sector',
        'added_by',
        'original_added_by',
        'reassigned_by',
        'reassigned_at',
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
        'reassigned_at' => 'datetime',
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
     * Get the original user who added this lead (before reassignment).
     */
    public function originalAddedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_added_by');
    }

    /**
     * Get the user who reassigned this lead.
     */
    public function reassignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reassigned_by');
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
     * Get all products for this lead.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(
                'product_name',
                'enrolled_at',
                'status',
                'notes',
                'value',
                'expected_close_date',
                'actual_close_date',
                'won_at',
                'lost_reason'
            )
            ->withTimestamps();
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

    /**
     * Get the status for a specific product.
     */
    public function getStatusForProduct(int $productId): ?string
    {
        $pivot = $this->products()->where('products.id', $productId)->first();
        return $pivot?->pivot->status;
    }

    /**
     * Get notes for a specific product.
     */
    public function getNotesForProduct(int $productId): ?string
    {
        $pivot = $this->products()->where('products.id', $productId)->first();
        return $pivot?->pivot->notes;
    }

    /**
     * Update status for a specific product.
     */
    public function updateStatusForProduct(int $productId, string $status): bool
    {
        $validStatuses = ['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $updateData = ['status' => $status];

        // If status is 'won', set won_at timestamp
        if ($status === 'won') {
            $updateData['won_at'] = now();
            $updateData['actual_close_date'] = now();
        }

        // If status is 'lost', ensure lost_reason is set (if not already)
        if ($status === 'lost' && !$this->getLostReasonForProduct($productId)) {
            $updateData['actual_close_date'] = now();
        }

        return $this->products()->updateExistingPivot($productId, $updateData) > 0;
    }

    /**
     * Add or update notes for a specific product.
     */
    public function addNoteForProduct(int $productId, string $note): bool
    {
        $existingNotes = $this->getNotesForProduct($productId);

        if ($existingNotes) {
            // Append to existing notes with timestamp
            $newNotes = $existingNotes . "\n\n--- " . now()->format('Y-m-d H:i:s') . " ---\n" . $note;
        } else {
            $newNotes = $note;
        }

        return $this->products()->updateExistingPivot($productId, [
            'notes' => $newNotes,
        ]) > 0;
    }

    /**
     * Replace notes for a specific product (overwrites existing notes).
     */
    public function setNotesForProduct(int $productId, string $notes): bool
    {
        return $this->products()->updateExistingPivot($productId, [
            'notes' => $notes,
        ]) > 0;
    }

    /**
     * Get value for a specific product.
     */
    public function getValueForProduct(int $productId): ?float
    {
        $pivot = $this->products()->where('products.id', $productId)->first();
        return $pivot?->pivot->value ? (float) $pivot->pivot->value : null;
    }

    /**
     * Update value for a specific product.
     */
    public function updateValueForProduct(int $productId, float $value): bool
    {
        return $this->products()->updateExistingPivot($productId, [
            'value' => $value,
        ]) > 0;
    }

    /**
     * Get expected close date for a specific product.
     */
    public function getExpectedCloseDateForProduct(int $productId): ?\Carbon\Carbon
    {
        $pivot = $this->products()->where('products.id', $productId)->first();
        return $pivot?->pivot->expected_close_date;
    }

    /**
     * Update expected close date for a specific product.
     */
    public function updateExpectedCloseDateForProduct(int $productId, $date): bool
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $this->products()->updateExistingPivot($productId, [
            'expected_close_date' => $date,
        ]) > 0;
    }

    /**
     * Get actual close date for a specific product.
     */
    public function getActualCloseDateForProduct(int $productId): ?\Carbon\Carbon
    {
        $pivot = $this->products()->where('products.id', $productId)->first();
        return $pivot?->pivot->actual_close_date;
    }

    /**
     * Get won_at timestamp for a specific product.
     */
    public function getWonAtForProduct(int $productId): ?\Carbon\Carbon
    {
        $pivot = $this->products()->where('products.id', $productId)->first();
        return $pivot?->pivot->won_at;
    }

    /**
     * Get lost reason for a specific product.
     */
    public function getLostReasonForProduct(int $productId): ?string
    {
        $pivot = $this->products()->where('products.id', $productId)->first();
        return $pivot?->pivot->lost_reason;
    }

    /**
     * Mark a specific product as won.
     */
    public function markProductAsWon(int $productId, ?float $value = null, $actualCloseDate = null): bool
    {
        $updateData = [
            'status' => 'won',
            'won_at' => now(),
            'actual_close_date' => $actualCloseDate ?? now(),
        ];

        if ($value !== null) {
            $updateData['value'] = $value;
        }

        return $this->products()->updateExistingPivot($productId, $updateData) > 0;
    }

    /**
     * Mark a specific product as lost.
     */
    public function markProductAsLost(int $productId, string $reason, $actualCloseDate = null): bool
    {
        return $this->products()->updateExistingPivot($productId, [
            'status' => 'lost',
            'lost_reason' => $reason,
            'actual_close_date' => $actualCloseDate ?? now(),
        ]) > 0;
    }

    /**
     * Get all product-specific data for a lead.
     */
    public function getProductData(int $productId): ?array
    {
        $pivot = $this->products()->where('products.id', $productId)->first();

        if (!$pivot) {
            return null;
        }

        return [
            'status' => $pivot->pivot->status,
            'notes' => $pivot->pivot->notes,
            'value' => $pivot->pivot->value,
            'expected_close_date' => $pivot->pivot->expected_close_date,
            'actual_close_date' => $pivot->pivot->actual_close_date,
            'won_at' => $pivot->pivot->won_at,
            'lost_reason' => $pivot->pivot->lost_reason,
            'enrolled_at' => $pivot->pivot->enrolled_at,
        ];
    }
}
