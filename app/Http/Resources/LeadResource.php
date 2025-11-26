<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'company' => $this->company,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'city' => $this->city,
            'country' => $this->country,
            'status' => $this->status,
            'value' => $this->value ? (float) $this->value : null,
            'product' => $this->product,
            'expected_close_date' => $this->expected_close_date?->format('Y-m-d'),
            'actual_close_date' => $this->actual_close_date?->format('Y-m-d'),
            'lost_reason' => $this->lost_reason,
            'won_at' => $this->won_at?->toISOString(),
            'is_client' => $this->is_client,
            'notes' => $this->notes,
            'added_by' => $this->whenLoaded('addedBy', function () {
                return new UserResource($this->addedBy);
            }),
            'added_by_id' => $this->added_by,
            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'follow_up_schedules' => $this->whenLoaded('followUpSchedules'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

