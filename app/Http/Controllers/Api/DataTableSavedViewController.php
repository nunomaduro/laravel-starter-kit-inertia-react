<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDataTableSavedViewRequest;
use App\Models\DataTableSavedView;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DataTableSavedViewController extends Controller
{
    /**
     * List grouped views for a table.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'table_name' => ['required', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $tableName = (string) $request->query('table_name');
        $orgId = TenantContext::id();

        $grouped = DataTableSavedView::grouped($tableName, $user->id, $orgId);

        return response()->json($grouped);
    }

    /**
     * Create a new saved view.
     */
    public function store(StoreDataTableSavedViewRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $data = $request->validated();
        $orgId = TenantContext::id();

        $isShared = $data['is_shared'] ?? false;
        $isSystem = $data['is_system'] ?? false;

        $view = DataTableSavedView::query()->create([
            'user_id' => $user->id,
            'table_name' => $data['table_name'],
            'name' => $data['name'],
            'filters' => $data['filters'] ?? null,
            'sort' => $data['sort'] ?? null,
            'columns' => $data['columns'] ?? null,
            'column_order' => $data['column_order'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'is_shared' => $isShared,
            'is_system' => $isSystem,
            'organization_id' => ($isShared || $isSystem) ? $orgId : null,
            'created_by' => $user->id,
        ]);

        return response()->json(['data' => $view], 201);
    }

    /**
     * Delete a saved view. Only the creator or an admin can delete.
     *
     * Enforces tenant isolation: shared/system views must belong to the
     * current org, and private views must belong to the authenticated user.
     */
    public function destroy(Request $request, DataTableSavedView $dataTableSavedView): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $orgId = TenantContext::id();

        // Tenant isolation: org-scoped views must belong to the current tenant
        if ($dataTableSavedView->organization_id !== null) {
            if ($dataTableSavedView->organization_id !== $orgId) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        } else {
            // Private views (no org) must belong to the authenticated user
            if ($dataTableSavedView->created_by !== $user->id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        $isCreator = $dataTableSavedView->created_by === $user->id;
        $isAdmin = $user->can('manage system views');

        if (! $isCreator && ! $isAdmin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $dataTableSavedView->delete();

        return response()->json(null, 204);
    }
}
