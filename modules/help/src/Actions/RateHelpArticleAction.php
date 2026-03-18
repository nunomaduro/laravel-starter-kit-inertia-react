<?php

declare(strict_types=1);

namespace Modules\Help\Actions;

use Modules\Help\Models\HelpArticle;

final readonly class RateHelpArticleAction
{
    public function handle(HelpArticle $article, bool $isHelpful): void
    {
        $article->increment($isHelpful ? 'helpful_count' : 'not_helpful_count');
    }
}
