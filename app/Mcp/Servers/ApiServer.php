<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\UsersIndexTool;
use App\Mcp\Tools\UsersShowTool;
use Laravel\Mcp\Server;
use Override;

final class ApiServer extends Server
{
    /**
     * The MCP server's name.
     */
    #[Override]
    protected string $name = 'Api Server';

    /**
     * The MCP server's version.
     */
    #[Override]
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    #[Override]
    protected string $instructions = <<<'MARKDOWN'
        This server exposes API capabilities as tools. Use users_index to list users with optional filters/sort, and users_show to get a single user by ID. All tools require an authenticated session (Sanctum).
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<Server\Tool>>
     */
    #[Override]
    protected array $tools = [
        UsersIndexTool::class,
        UsersShowTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<Server\Resource>>
     */
    #[Override]
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<Server\Prompt>>
     */
    #[Override]
    protected array $prompts = [
        //
    ];
}
