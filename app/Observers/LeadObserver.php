<?php

namespace App\Observers;

use App\Models\Lead;

class LeadObserver
{
    /**
     * Handle the Lead "updating" event.
     * Automatically convert lead to client when status changes to 'won'.
     */
    public function updating(Lead $lead): void
    {
        // Check if status is being changed to 'won'
        if ($lead->isDirty('status') && $lead->status === 'won') {
            // Only set is_client and won_at if they haven't been set already
            // or if the lead wasn't previously won
            if ($lead->getOriginal('status') !== 'won') {
                $lead->is_client = true;
                
                // Only set won_at if it's not already set
                if (is_null($lead->won_at)) {
                    $lead->won_at = now();
                }
            }
        }
        
        // If status is being changed away from 'won', we might want to handle that too
        // But for now, we'll leave is_client as true even if status changes (historical record)
    }
}

