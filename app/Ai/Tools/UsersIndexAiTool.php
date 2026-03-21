<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class UsersIndexAiTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List users with optional filters (name, email), sort, per_page. Returns paginated user list.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = User::query();

        $filterName = $request->get('filter_name');
        if ($filterName !== null) {
            $query->where('name', 'like', '%'.$filterName.'%');
        }

        $filterEmail = $request->get('filter_email');
        if ($filterEmail !== null) {
            $query->where('email', 'like', '%'.$filterEmail.'%');
        }

        $sort = $request->get('sort');
        if ($sort !== null && $sort !== '') {
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $query->orderBy(mb_ltrim($sort, '-'), $direction);
        }

        $perPage = $request->get('per_page', 15);

        $users = $query->paginate($perPage);

        return json_encode(UserResource::collection($users)->response()->getData(true), JSON_THROW_ON_ERROR);
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'filter_name' => $schema->string()->description('Filter users by name (partial match).'),
            'filter_email' => $schema->string()->description('Filter users by email (partial match).'),
            'sort' => $schema->string()->description('Sort field. Prefix with - for descending (e.g. -created_at).'),
            'per_page' => $schema->integer()->description('Number of results per page. Defaults to 15.'),
        ];
    }
}
