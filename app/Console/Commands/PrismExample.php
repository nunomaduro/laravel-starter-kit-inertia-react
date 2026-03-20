<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PrismService;
use Exception;
use Illuminate\Console\Command;

use function Laravel\Ai\agent;
use function Laravel\Prompts\textarea;

final class PrismExample extends Command
{
    protected $signature = 'prism:example
                            {--prompt= : Prompt to send (or will prompt interactively)}
                            {--tools= : Comma-separated list of MCP server names (uses Relay)}';

    protected $description = 'Example command demonstrating laravel/ai agents and Relay MCP tools';

    public function handle(PrismService $prism): int
    {
        $prompt = $this->option('prompt') ?? textarea('Enter your prompt:', required: true);
        $tools = $this->option('tools');

        $this->info('Sending request...');
        $this->newLine();

        try {
            if ($tools) {
                // MCP tools path — requires Relay (no laravel/ai equivalent)
                $servers = array_map(trim(...), explode(',', $tools));
                $response = $prism->withTools($servers)->withPrompt($prompt)->asText();

                $this->info('Response (with MCP tools):');
                $this->line($response->text);

                return self::SUCCESS;
            }

            // Standard text generation via laravel/ai agent
            $response = agent(instructions: 'You are a helpful assistant.')->prompt($prompt);

            $this->info('Response:');
            $this->line($response->text);

            return self::SUCCESS;
        } catch (Exception $exception) {
            $this->error('Error: '.$exception->getMessage());

            if ($this->option('verbose')) {
                $this->line($exception->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
