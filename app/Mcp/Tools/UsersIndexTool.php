<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

final class UsersIndexTool extends Tool
{
    protected string $name = 'users_index';

    protected string $title = 'List users';

    protected string $description = <<<'MARKDOWN'
        List users with optional filters (name, email), sort, per_page, and include (e.g. roles).
    MARKDOWN;

    public function handle(Request $request): Response
    {
        $query = User::query();

        if ($request->get('filter_name') !== null) {
            $query->where('name', 'like', '%'.($request->get('filter_name')).'%');
        }

        if ($request->get('filter_email') !== null) {
            $query->where('email', 'like', '%'.($request->get('filter_email')).'%');
        }

        $sort = $request->get('sort');
        if (is_string($sort) && $sort !== '') {
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $query->orderBy(mb_ltrim($sort, '-'), $direction);
        }

        $include = $request->get('include');
        if (is_string($include) && $include !== '') {
            $query->with(array_map(trim(...), explode(',', $include)));
        }

        $perPage = (int) $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        $data = UserResource::collection($users)->response()->getData(true);

        return Response::json($data);
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'filter_name' => $schema->string()->description('Partial match on name')->nullable(),
            'filter_email' => $schema->string()->description('Partial match on email')->nullable(),
            'sort' => $schema->string()->description('Sort column, prefix with - for desc (e.g. -created_at)')->nullable(),
            'per_page' => $schema->integer()->description('Items per page')->nullable(),
            'include' => $schema->string()->description('Comma-separated includes, e.g. roles')->nullable(),
        ];
    }
}
