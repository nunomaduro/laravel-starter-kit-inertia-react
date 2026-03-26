<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Controllers;

use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\BotStudio\Http\Requests\StoreAgentReviewRequest;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentInstall;
use Modules\BotStudio\Models\AgentReview;

final readonly class MarketplaceController
{
    public function index(Request $request): Response
    {
        $query = AgentDefinition::query()
            ->where('is_published', true)
            ->with('creator');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($category = $request->string('category')->toString()) {
            $query->where('category', $category);
        }

        $sort = $request->string('sort', 'popular')->toString();

        $query->orderByDesc(match ($sort) {
            'rating' => 'average_rating',
            'newest' => 'created_at',
            default => 'install_count',
        });

        $agents = $query->paginate(12)->withQueryString();

        $featured = AgentDefinition::query()
            ->where('is_published', true)
            ->where('is_featured', true)
            ->with('creator')
            ->orderByDesc('install_count')
            ->limit(6)
            ->get();

        $categories = AgentDefinition::query()
            ->where('is_published', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return Inertia::render('bot-studio/marketplace/index', [
            'agents' => $agents,
            'featured' => $featured,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(AgentDefinition $agentDefinition): Response
    {
        abort_unless($agentDefinition->is_published, 404);

        $agentDefinition->load('creator');

        $reviews = $agentDefinition->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        $isInstalled = AgentInstall::query()
            ->where('organization_id', TenantContext::id())
            ->where('agent_definition_id', $agentDefinition->id)
            ->exists();

        $userReview = AgentReview::query()
            ->where('agent_definition_id', $agentDefinition->id)
            ->where('user_id', auth()->id())
            ->first();

        return Inertia::render('bot-studio/marketplace/show', [
            'definition' => $agentDefinition,
            'reviews' => $reviews,
            'isInstalled' => $isInstalled,
            'userReview' => $userReview,
        ]);
    }

    public function install(AgentDefinition $agentDefinition): RedirectResponse
    {
        abort_unless($agentDefinition->is_published, 404);

        $orgId = TenantContext::id();

        $alreadyInstalled = AgentInstall::query()
            ->where('organization_id', $orgId)
            ->where('agent_definition_id', $agentDefinition->id)
            ->exists();

        abort_if($alreadyInstalled, 409, __('This agent is already installed.'));

        $copy = $agentDefinition->replicate([
            'slug',
            'is_published',
            'is_featured',
            'is_template',
            'total_conversations',
            'total_messages',
            'average_rating',
            'review_count',
            'install_count',
        ]);

        $copy->fill([
            'organization_id' => $orgId,
            'created_by' => auth()->id(),
            'is_template' => false,
            'is_published' => false,
            'is_featured' => false,
            'cloned_from' => $agentDefinition->id,
        ]);

        $copy->save();

        AgentInstall::query()->create([
            'organization_id' => $orgId,
            'agent_definition_id' => $agentDefinition->id,
            'installed_definition_id' => $copy->id,
            'installed_by' => auth()->id(),
        ]);

        $agentDefinition->increment('install_count');

        return to_route('bot-studio.edit', $copy)
            ->with('status', __('Agent installed successfully.'));
    }

    public function review(StoreAgentReviewRequest $request, AgentDefinition $agentDefinition): RedirectResponse
    {
        abort_unless($agentDefinition->is_published, 404);
        abort_if($agentDefinition->created_by === auth()->id(), 403, __('You cannot review your own agent.'));

        AgentReview::query()->updateOrCreate(
            [
                'agent_definition_id' => $agentDefinition->id,
                'user_id' => auth()->id(),
            ],
            [
                'organization_id' => TenantContext::id(),
                'rating' => $request->validated('rating'),
                'review' => $request->validated('review'),
            ],
        );

        return back()->with('status', __('Review submitted.'));
    }

    public function deleteReview(AgentDefinition $agentDefinition): RedirectResponse
    {
        AgentReview::query()
            ->where('agent_definition_id', $agentDefinition->id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('status', __('Review deleted.'));
    }
}
