<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'category',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all leads that have this product.
     */
    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class)
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
     * Get leads for this product filtered by status.
     */
    public function getLeadsByStatus(string $status)
    {
        return $this->leads()->wherePivot('status', $status)->get();
    }

    /**
     * Get active leads for this product (not won or lost).
     */
    public function getActiveLeads()
    {
        return $this->leads()
            ->wherePivotNotIn('status', ['won', 'lost'])
            ->get();
    }

    /**
     * Get won leads for this product.
     */
    public function getWonLeads()
    {
        return $this->leads()->wherePivot('status', 'won')->get();
    }

    /**
     * Get lost leads for this product.
     */
    public function getLostLeads()
    {
        return $this->leads()->wherePivot('status', 'lost')->get();
    }

    /**
     * Get total pipeline value for this product (sum of all active lead values).
     */
    public function getTotalPipelineValue(): float
    {
        return $this->leads()
            ->wherePivotNotIn('status', ['won', 'lost'])
            ->wherePivotNotNull('value')
            ->sum('lead_product.value') ?? 0;
    }

    /**
     * Get total won value for this product.
     */
    public function getTotalWonValue(): float
    {
        return $this->leads()
            ->wherePivot('status', 'won')
            ->wherePivotNotNull('value')
            ->sum('lead_product.value') ?? 0;
    }
}
