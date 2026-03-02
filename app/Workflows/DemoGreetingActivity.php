<?php

declare(strict_types=1);

namespace App\Workflows;

use Workflow\Activity;

/**
 * Example activity for the durable workflow demo.
 * Returns a greeting string; used by DemoGreetingWorkflow.
 */
final class DemoGreetingActivity extends Activity
{
    public function execute(string $name): string
    {
        return "Hello, {$name}!";
    }
}
