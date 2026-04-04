<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'is_active'     => $this->is_active,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at'    => $this->created_at->toISOString(),
            'updated_at'    => $this->updated_at->toISOString(),
            'deleted_at'    => $this->deleted_at?->toISOString(),

            // Branch summary
            'branch' => $this->whenLoaded('branch', fn () => [
                'id'   => $this->branch->id,
                'name' => $this->branch->name,
                'city' => $this->branch->city,
            ]),

            // Employee summary
            'employee' => $this->whenLoaded('employee', fn () => $this->employee ? [
                'id'            => $this->employee->id,
                'employee_code' => $this->employee->employee_code,
                'full_name'     => $this->employee->full_name,
                'role'          => $this->employee->role,
            ] : null),

            // Spatie roles & permissions
            'roles'       => $this->whenLoaded('roles', fn () =>
                $this->roles->pluck('name')
            ),
            'permissions' => $this->whenLoaded('permissions', fn () =>
                $this->getAllPermissions()->pluck('name')
            ),
        ];
    }
}
