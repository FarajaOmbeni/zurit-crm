<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EODReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'report_date' => $this->report_date?->format('Y-m-d'),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'highlights' => $this->highlights,
            'challenges' => $this->challenges,
            'file_path' => $this->file_path,
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'user_id' => $this->user_id,
            'salesperson_name' => $this->whenLoaded('user', fn() => $this->user->name),
            'outreach_summary' => $data['outreach_summary'] ?? [
                'schemes_contacted' => 0,
                'schemes_newly_engaged' => 0,
                'follow_ups_conducted' => 0,
                'active_pipeline' => 0,
            ],
            'schemes_engagement' => $data['schemes_engagement'] ?? [],
            'new_leads' => $data['new_leads'] ?? [],
            'won_deals' => $data['won_deals'] ?? [],
            'lost_deals' => $data['lost_deals'] ?? [],
            'key_reminders' => $data['key_reminders'] ?? [
                'upcoming_tasks' => [],
                'overdue_tasks' => [],
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

