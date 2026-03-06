<?php

declare(strict_types=1);

namespace App\Workflows;

use Workflow\Workflow;

use function Workflow\activity;

/**
 * Example workflow that runs DemoGreetingActivity.
 * Start with: WorkflowStub::make(DemoGreetingWorkflow::class)->start('world');
 * Monitor at /waterline (admin only).
 */
final class DemoGreetingWorkflow extends Workflow
{
    public function execute(string $name)
    {
        return yield activity(DemoGreetingActivity::class, $name);
    }
}
