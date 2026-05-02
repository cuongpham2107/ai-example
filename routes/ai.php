<?php

use App\Mcp\Servers\HumanServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/human', HumanServer::class);
