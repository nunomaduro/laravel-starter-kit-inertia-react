<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateUser;
use App\Actions\DeleteUser;
use App\Actions\UpdateUser;
use App\Http\Requests\Api\V1\BatchUserRequest;
use App\Http\Requests\Api\V1\SearchUserRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class UserController extends BaseApiController
{
    /**
     * List users. Supports filter, sort, include, and fields query parameters.
     *
     * Query params: filter[name], filter[email], sort, include (e.g. roles), fields[users]
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFields([
                'id', 'name', 'email', 'phone', 'email_verified_at', 'created_at', 'updated_at',
            ])
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
            ])
            ->allowedSorts(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->allowedIncludes(['roles'])
            ->paginate($request->input('per_page', 15))
            ->withQueryString();

        return UserResource::collection($users);
    }

    /**
     * Show a single user.
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return $this->responseSuccess(null, new UserResource($user));
    }

    /**
     * Create a user.
     */
    public function store(CreateUserRequest $request, CreateUser $action): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();
        $password = (string) $validated['password'];
        unset($validated['password'], $validated['password_confirmation']);

        $user = $action->handle($validated, $password);

        return $this->responseCreated('User created successfully', new UserResource($user));
    }

    /**
     * Update a user.
     */
    public function update(UpdateUserRequest $request, User $user, UpdateUser $action): JsonResponse
    {
        $this->authorize('update', $user);

        $action->handle($user, $request->validated(), $request);

        return $this->responseSuccess(null, new UserResource($user->fresh()));
    }

    /**
     * Delete a user.
     */
    public function destroy(DeleteUserRequest $request, User $user, DeleteUser $action): JsonResponse
    {
        $this->authorize('delete', $user);

        $action->handle($user);

        return $this->responseDeleted();
    }

    /**
     * Batch create, update, and/or delete users.
     */
    public function batch(
        BatchUserRequest $request,
        CreateUser $createUser,
        UpdateUser $updateUser,
        DeleteUser $deleteUser
    ): JsonResponse {
        $created = [];
        $updated = [];
        $deleted = [];

        DB::transaction(function () use ($request, $createUser, $updateUser, $deleteUser, &$created, &$updated, &$deleted): void {
            $validated = $request->validated();

            foreach ($request->input('create', []) as $item) {
                $this->authorize('create', User::class);
                $password = (string) $item['password'];
                unset($item['password']);
                $user = $createUser->handle($item, $password);
                $created[] = $user->id;
            }

            foreach ($request->input('update', []) as $item) {
                $user = User::query()->findOrFail($item['id']);
                $this->authorize('update', $user);
                $attrs = array_diff_key($item, ['id' => true]);
                if ($attrs !== []) {
                    $updateUser->handle($user, $attrs, $request);
                    $updated[] = $user->id;
                }
            }

            foreach ($request->input('delete', []) as $id) {
                $user = User::query()->findOrFail($id);
                $this->authorize('delete', $user);
                $deleteUser->handle($user);
                $deleted[] = $id;
            }
        });

        return $this->responseSuccess(null, [
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
        ]);
    }

    /**
     * Search users via POST body (filters, sort, pagination, include).
     */
    public function search(SearchUserRequest $request): AnonymousResourceCollection
    {
        $filters = $request->input('filters', []);
        $sort = $request->input('sort', '-created_at');
        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);
        $include = $request->input('include', []);

        $query = User::query();

        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, 'like', str_contains((string) $value, '%') ? $value : '%'.$value.'%');
            }
        }

        if ($sort !== '') {
            $direction = str_starts_with((string) $sort, '-') ? 'desc' : 'asc';
            $column = mb_ltrim($sort, '-');
            $query->orderBy($column, $direction);
        }

        if ($include !== []) {
            $query->with($include);
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        return UserResource::collection($users);
    }
}
