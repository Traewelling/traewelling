<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\TelegramException;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Tests\Unit\UnitTestCase;

class TelegramServiceTest extends UnitTestCase
{
    private TelegramService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TelegramService(chatId: '123456', token: 'test-token');
    }

    public function test_send_message_returns_message_id_on_success(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(
                ['ok' => true, 'result' => ['message_id' => 99]],
                200,
            ),
        ]);

        $messageId = $this->service->sendMessage('Hello');

        $this->assertSame(99, $messageId);
    }

    public function test_send_message_throws_on_api_error(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response('Bad Request', 400),
        ]);

        $this->expectException(TelegramException::class);

        $this->service->sendMessage('Hello');
    }

    public function test_delete_message_returns_true_on_success(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $this->assertTrue($this->service->deleteMessage(99));
    }

    public function test_delete_message_returns_false_on_api_error(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response('Bad Request', 400),
        ]);

        $this->assertFalse($this->service->deleteMessage(99));
    }

    public function test_is_admin_active_reflects_config(): void
    {
        config(['services.telegram.admin.active' => true]);
        $this->assertTrue(TelegramService::isAdminActive());

        config(['services.telegram.admin.active' => false]);
        $this->assertFalse(TelegramService::isAdminActive());
    }

    public function test_admin_factory_returns_configured_instance(): void
    {
        config([
            'services.telegram.admin.active' => true,
            'services.telegram.admin.chat_id' => '999',
            'services.telegram.admin.token' => 'admin-token',
        ]);

        $admin = TelegramService::admin();

        $this->assertInstanceOf(TelegramService::class, $admin);
        $this->assertSame('999', $admin->chatId);
    }
}
