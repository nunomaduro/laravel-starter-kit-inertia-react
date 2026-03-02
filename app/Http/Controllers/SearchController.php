<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChangelogEntry;
use App\Models\HelpArticle;
use App\Models\Post;
use App\Models\User;
use App\Services\TenantContext;
use App\Support\FeatureHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchController
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = mb_trim((string) $request->query('q', ''));
        $type = $request->query('type');

        if ($query === '') {
            return response()->json([
                'users' => [],
                'posts' => [],
                'help_articles' => [],
                'changelog_entries' => [],
            ]);
        }

        $results = [];
        $totalLimit = 20;
        $perCategory = 5;
        $total = 0;

        if ($this->shouldSearch('users', $type) && $total < $totalLimit) {
            $users = User::search($query)
                ->query(fn (Builder $builder) => $builder->whereHas(
                    'organizations',
                    fn (Builder $q) => $q->where('organizations.id', TenantContext::id())
                ))
                ->take($perCategory)
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'url' => route('users.show', $user),
                    'type' => 'user',
                ]);
            $results['users'] = $users->all();
            $total += $users->count();
        } else {
            $results['users'] = [];
        }

        if ($this->shouldSearch('posts', $type) && FeatureHelper::isActiveForKey('blog') && $total < $totalLimit) {
            $remaining = min($perCategory, $totalLimit - $total);
            $posts = Post::search($query)
                ->query(fn (Builder $builder) => $builder->published())
                ->take($remaining)
                ->get()
                ->map(fn (Post $post) => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'subtitle' => $post->excerpt ?? '',
                    'url' => route('blog.show', $post),
                    'type' => 'post',
                ]);
            $results['posts'] = $posts->all();
            $total += $posts->count();
        } else {
            $results['posts'] = [];
        }

        if ($this->shouldSearch('help_articles', $type) && FeatureHelper::isActiveForKey('help') && $total < $totalLimit) {
            $remaining = min($perCategory, $totalLimit - $total);
            $helpArticles = HelpArticle::search($query)
                ->query(fn (Builder $builder) => $builder->published())
                ->take($remaining)
                ->get()
                ->map(fn (HelpArticle $article) => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'subtitle' => $article->excerpt ?? '',
                    'url' => route('help.show', $article),
                    'type' => 'help_article',
                ]);
            $results['help_articles'] = $helpArticles->all();
            $total += $helpArticles->count();
        } else {
            $results['help_articles'] = [];
        }

        if ($this->shouldSearch('changelog_entries', $type) && FeatureHelper::isActiveForKey('changelog') && $total < $totalLimit) {
            $remaining = min($perCategory, $totalLimit - $total);
            $changelogEntries = ChangelogEntry::search($query)
                ->query(fn (Builder $builder) => $builder->published())
                ->take($remaining)
                ->get()
                ->map(fn (ChangelogEntry $entry) => [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'subtitle' => $entry->version ? "v{$entry->version}" : '',
                    'url' => route('changelog.index'),
                    'type' => 'changelog_entry',
                ]);
            $results['changelog_entries'] = $changelogEntries->all();
        } else {
            $results['changelog_entries'] = [];
        }

        return response()->json($results);
    }

    private function shouldSearch(string $category, mixed $type): bool
    {
        if ($type === null || $type === '') {
            return true;
        }

        return $type === $category;
    }
}
