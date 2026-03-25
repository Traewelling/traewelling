<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TelegramException;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    const TELEGRAM_API_URL = 'https://api.telegram.org/bot';

    public readonly string $chatId;

    private readonly string $token;

    public function __construct(string $chatId, string $token)
    {
        $this->chatId = $chatId;
        $this->token = $token;
    }

    public static function adminForEvents(): ?self
    {
        $token = config('services.telegram.admin.token');
        $chatId = config('services.telegram.admin.events_chat_id');
        if (!filled($token) || !filled($chatId)) {
            return null;
        }

        return new self($chatId, $token);
    }

    public static function adminForReports(): ?self
    {
        $token = config('services.telegram.admin.token');
        $chatId = config('services.telegram.admin.reports_chat_id');
        if (!filled($token) || !filled($chatId)) {
            return null;
        }

        return new self($chatId, $token);
    }

    /**
     * @return int Telegram Message ID
     *
     * @throws TelegramException
     */
    public function sendMessage(string $text, string $parseMode = 'HTML'): int
    {
        $response = Http::post(self::TELEGRAM_API_URL . $this->token . '/sendMessage', [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ]);
        if (!$response->ok()) {
            throw new TelegramException('Telegram API error: ' . $response->body());
        }

        return $response->json('result.message_id');
    }

    public function deleteMessage(int $messageId): bool
    {
        $response = Http::post(self::TELEGRAM_API_URL . $this->token . '/deleteMessage', [
            'chat_id' => $this->chatId,
            'message_id' => $messageId,
        ]);

        return $response->ok();
    }
}
