<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

final class UsersShowTool extends Tool
{
    protected string $name = 'users_show';

    protected string $title = 'Show user';

    protected string $description = <<<'MARKDOWN'
        Get a single user by ID.
    MARKDOWN;

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if ($id === null) {
            return Response::error('Missing required parameter: id');
        }

        $user = User::query()->find($id);
        if ($user === null) {
            return Response::error(sprintf('User with id %s not found.', $id));
        }

        $data = new UserResource($user)->response()->getData(true);

        return Response::json($data);
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('User ID')->required(),
        ];
    }
}
