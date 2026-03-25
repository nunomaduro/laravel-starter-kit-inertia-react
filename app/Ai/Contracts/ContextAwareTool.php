<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

interface ContextAwareTool
{
    /** @param  array{page?: string, entity_type?: string, entity_id?: int, entity_name?: string}  $context */
    public function setContext(array $context): void;
}
