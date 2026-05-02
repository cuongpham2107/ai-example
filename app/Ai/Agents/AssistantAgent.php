<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetHumanInfoTool;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class AssistantAgent implements Agent, HasTools, Conversational
{
    use Promptable, RemembersConversations;

    /**
     * Lấy hướng dẫn cho agent.
     */
    public function instructions(): string
    {
        return 'Bạn là một trợ lý ảo hữu ích. Bạn có thể sử dụng công cụ get_human_info để tra cứu thông tin về mọi người nếu người dùng cung cấp mã ASGL ID.';
    }

    /**
     * Lấy các công cụ mà agent có thể sử dụng.
     */
    public function tools(): iterable
    {
        return [
            new GetHumanInfoTool,
        ];
    }
}
