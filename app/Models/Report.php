<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'report_date',
        'start_date',
        'end_date',
        'highlights',
        'challenges',
        'data',
        'file_path',
    ];

    protected $casts = [
        'report_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'data' => 'array',
    ];

    /**
     * Get the user who generated this report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
