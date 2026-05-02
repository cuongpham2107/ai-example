<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get-information-human')]
#[Title('Lấy thông tin người')]
#[Description('Truy xuất thông tin về một người bằng mã ASGL ID của họ.')]
class GetInforHumanTool extends Tool
{
    /**
     * Xử lý yêu cầu công cụ.
     */
    public function handle(Request $request): Response
    {
        $asglId = $request->get('asgl_id');
        $apiKey = env('ASGL_API_KEY');

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->get("https://id.asgl.net.vn/api/internal/users/by-asgl-id/{$asglId}");

            if ($response->failed()) {
                return Response::text("Không tìm thấy thông tin cho mã ID: {$asglId} hoặc có lỗi xảy ra từ máy chủ.");
            }

            $data = $response->json();
            $user = $data['data']['user'] ?? null;

            if (!$user) {
                return Response::text("Không tìm thấy dữ liệu người dùng trong phản hồi từ máy chủ cho mã ID: {$asglId}.");
            }

            $fullName = $user['full_name'] ?? 'N/A';
            $username = $user['asgl_id'] ?? 'N/A';
            $phone = $user['mobile_phone'] ?? 'N/A';
            
            $positions = collect($user['positions'] ?? [])->map(function ($pos) {
                $deptName = $pos['department']['name'] ?? 'N/A';
                return "- {$pos['name']} ({$deptName})";
            })->implode("\n");

            $output = "Thông tin nhân viên cho mã ID {$asglId}:\n";
            $output .= "- Họ tên: {$fullName}\n";
            $output .= "- Mã ASGL: {$username}\n";
            $output .= "- Điện thoại: {$phone}\n";
            $output .= "- Chức vụ:\n{$positions}";

            return Response::text($output);
        } catch (\Exception $e) {
            return Response::text('Lỗi khi gọi API: '.$e->getMessage());
        }
    }

    /**
     * Lấy schema đầu vào của công cụ.
     */
    public function schema(JsonSchema $schema): array
    {
        // Đầu vào của tool, có thể có nhiều trường khác nhau tùy theo yêu cầu của bạn. Dưới đây là một ví dụ về schema với một trường "asgl_id" kiểu string.
        return [
            'asgl_id' => $schema->string()->description('Mã ASGL ID.')->required(),

        ];
    }
}
