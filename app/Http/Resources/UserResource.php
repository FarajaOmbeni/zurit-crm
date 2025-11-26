<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role,
            'manager_id' => $this->manager_id,
            'is_active' => $this->is_active,
            'must_reset_password' => $this->must_reset_password,
            'manager' => $this->whenLoaded('manager', function () {
                return new UserResource($this->manager);
            }),
            'team_members' => UserResource::collection($this->whenLoaded('teamMembers')),
            'leads_count' => $this->when(isset($this->leads_count), $this->leads_count),
            'clients_count' => $this->when(isset($this->clients_count), $this->clients_count),
            'tasks_count' => $this->when(isset($this->tasks_count), $this->tasks_count),
            'activities_count' => $this->when(isset($this->activities_count), $this->activities_count),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

