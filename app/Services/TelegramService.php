<?php declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{

    private string $chatId;
    private string $token;

    public function __construct(string $chatId, string $token) {
        $this->chatId = $chatId;
        $this->token  = $token;
    }

    public static function isAdminActive(): bool {
        return config('services.telegram.admin.active');
    }
 
    public static function admin(): self {
        return new self(config('services.telegram.admin.chat_id'), config('services.telegram.admin.token'));
    }

    public function sendMessage(string $text, string $parseMode = 'HTML'): int {
        $response = Http::post('https://api.telegram.org/bot' . $this->token . '/sendMessage', [
            'chat_id'    => $this->chatId,
            'text'       => $text,
            'parse_mode' => $parseMode,
        ]);
        return $response->json('result.message_id');
    }

    public function deleteMessage(int $messageId): void {
        Http::post('https://api.telegram.org/bot' . $this->token . '/deleteMessage', [
            'chat_id'    => $this->chatId,
            'message_id' => $messageId,
        ]);
    }
}
