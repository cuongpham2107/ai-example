<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Human Server')]
#[Version('0.0.1')]
#[Instructions('Instructions describing how to use the server and its features.')]
class HumanServer extends Server
{
    protected array $tools = [
        GetInforHumanTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        DescribeHumanPrompt::class,
    ];
}
