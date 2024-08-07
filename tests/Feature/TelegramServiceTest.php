<?php

namespace Tests\Feature;

use App\Exceptions\TelegramException;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Tests\FeatureTestCase;

class TelegramServiceTest extends FeatureTestCase
{

    private const CHAT_ID = '123456789';
    private const TOKEN   = '123456789:ABC-DEF1234ghIkl-zyx57W2v1u123ew11';

    public function testSendTelegramMessage(): void {
        Http::fake(['https://api.telegram.org/bot' . self::TOKEN . '/sendMessage' => Http::response(['result' => ['message_id' => 123]])]);
        $telegramService = new TelegramService(self::CHAT_ID, self::TOKEN);
        $messageId       = $telegramService->sendMessage('Hello, World!');
        $this->assertIsInt($messageId);
    }

    public function testSendTelegramMessageWithWrongCredentials(): void {
        Http::fake([
                       'https://api.telegram.org/bot' . self::TOKEN . '/sendMessage' => Http::response(
                           body:   [
                                       'ok'          => false,
                                       'error_code'  => 401,
                                       'description' => 'Unauthorized',
                                   ],
                           status: 401
                       )
                   ]);
        $telegramService = new TelegramService(self::CHAT_ID, self::TOKEN);
        $this->expectException(TelegramException::class);
        $telegramService->sendMessage('Hello, World!');
    }

    public function testDeleteTelegramMessage(): void {
        Http::fake(['https://api.telegram.org/bot' . self::TOKEN . '/deleteMessage' => Http::response()]);
        $telegramService = new TelegramService(self::CHAT_ID, self::TOKEN);
        $this->assertTrue($telegramService->deleteMessage(123));
    }

    public function testAdminChatSelector(): void {
        config(['services.telegram.admin.active' => true]);
        $this->assertTrue(TelegramService::isAdminActive());
        
        config(['services.telegram.admin.chat_id' => self::CHAT_ID]);
        config(['services.telegram.admin.token' => self::TOKEN]);
        $telegramService = TelegramService::admin();
        $this->assertEquals(self::CHAT_ID, $telegramService->chatId);
    }
}
