<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class UsersShowAiTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get a single user by ID. Returns the full user resource.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $id = $request->get('id');
        if ($id === null) {
            return 'Missing required parameter: id';
        }

        $user = User::query()->find($id);
        if ($user === null) {
            return sprintf('User with id %s not found.', $id);
        }

        $data = (new UserResource($user))->response()->getData(true);

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('User ID')->required(),
        ];
    }
}
