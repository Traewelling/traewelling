<?php

namespace Tests\Feature;

use App\Exceptions\TelegramException;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Tests\FeatureTestCase;

class TelegramServiceTest extends FeatureTestCase
{
    private const CHAT_ID = '123456789';

    private const TOKEN = '123456789:ABC-DEF1234ghIkl-zyx57W2v1u123ew11';

    public function test_send_telegram_message(): void
    {
        Http::fake(['https://api.telegram.org/bot' . self::TOKEN . '/sendMessage' => Http::response(['result' => ['message_id' => 123]])]);
        $telegramService = new TelegramService(self::CHAT_ID, self::TOKEN);
        $messageId = $telegramService->sendMessage('Hello, World!');
        $this->assertIsInt($messageId);
    }

    public function test_send_telegram_message_with_wrong_credentials(): void
    {
        Http::fake([
            'https://api.telegram.org/bot' . self::TOKEN . '/sendMessage' => Http::response(
                body: [
                    'ok' => false,
                    'error_code' => 401,
                    'description' => 'Unauthorized',
                ],
                status: 401
            ),
        ]);
        $telegramService = new TelegramService(self::CHAT_ID, self::TOKEN);
        $this->expectException(TelegramException::class);
        $telegramService->sendMessage('Hello, World!');
    }

    public function test_delete_telegram_message(): void
    {
        Http::fake(['https://api.telegram.org/bot' . self::TOKEN . '/deleteMessage' => Http::response()]);
        $telegramService = new TelegramService(self::CHAT_ID, self::TOKEN);
        $this->assertTrue($telegramService->deleteMessage(123));
    }

    public function test_admin_for_events_returns_configured_instance(): void
    {
        config([
            'services.telegram.admin.token' => self::TOKEN,
            'services.telegram.admin.events_chat_id' => self::CHAT_ID,
        ]);

        $telegramService = TelegramService::adminForEvents();

        $this->assertNotNull($telegramService);
        $this->assertEquals(self::CHAT_ID, $telegramService->chatId);
    }

    public function test_admin_for_reports_returns_configured_instance(): void
    {
        config([
            'services.telegram.admin.token' => self::TOKEN,
            'services.telegram.admin.reports_chat_id' => self::CHAT_ID,
        ]);

        $telegramService = TelegramService::adminForReports();

        $this->assertNotNull($telegramService);
        $this->assertEquals(self::CHAT_ID, $telegramService->chatId);
    }
}
