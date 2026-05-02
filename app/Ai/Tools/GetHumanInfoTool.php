<?php

namespace App\Ai\Tools;

use App\Mcp\Tools\GetInforHumanTool as McpGetInforHumanTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Laravel\Mcp\Request as McpRequest;
use Stringable;

class GetHumanInfoTool implements Tool
{
    /**
     * Lấy mô tả về mục đích của công cụ.
     */
    public function description(): Stringable|string
    {
        return 'Truy xuất thông tin về một người bằng mã ASGL ID của họ.';
    }

    /**
     * Thực thi công cụ.
     */
    public function handle(Request $request): Stringable|string
    {
        $asglId = $request['asgl_id'];

        // Ủy quyền cho công cụ MCP để đảm bảo tính nhất quán
        $mcpTool = app(McpGetInforHumanTool::class);
        $mcpRequest = new McpRequest(['asgl_id' => $asglId]);

        $response = $mcpTool->handle($mcpRequest);

        return (string) $response->content();
    }

    /**
     * Lấy định nghĩa schema của công cụ.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'asgl_id' => $schema->string()->description('Mã ASGL ID của người cần tra cứu.')->required(),
        ];
    }
}
