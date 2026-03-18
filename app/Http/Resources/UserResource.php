<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\QueryBuilderRequest;

/**
 * @mixin \App\Models\User
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $requestedFields = $this->getRequestedFields($request);
        $all = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
        ];

        if ($requestedFields === null) {
            return $all;
        }

        $filtered = array_intersect_key($all, array_fill_keys($requestedFields, true));
        if ($this->relationLoaded('roles')) {
            $filtered['roles'] = $all['roles'];
        }

        return $filtered;
    }

    /**
     * @return array<string>|null Requested field names for users, or null for all.
     */
    private function getRequestedFields(Request $request): ?array
    {
        $fields = QueryBuilderRequest::fromRequest($request)->fields();

        if ($fields->isEmpty()) {
            return null;
        }

        return $fields->get('users') ?? $fields->get('_');
    }
}
