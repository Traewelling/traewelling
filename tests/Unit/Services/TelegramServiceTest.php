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

    public function test_admin_for_events_returns_null_when_token_missing(): void
    {
        config(['services.telegram.admin.token' => null, 'services.telegram.admin.events_chat_id' => '123']);

        $this->assertNull(TelegramService::adminForEvents());
    }

    public function test_admin_for_events_returns_null_when_chat_id_missing(): void
    {
        config(['services.telegram.admin.token' => 'token', 'services.telegram.admin.events_chat_id' => null]);

        $this->assertNull(TelegramService::adminForEvents());
    }

    public function test_admin_for_events_returns_instance_when_configured(): void
    {
        config([
            'services.telegram.admin.token' => 'admin-token',
            'services.telegram.admin.events_chat_id' => '999',
        ]);

        $service = TelegramService::adminForEvents();

        $this->assertInstanceOf(TelegramService::class, $service);
        $this->assertSame('999', $service->chatId);
    }

    public function test_admin_for_reports_returns_instance_when_configured(): void
    {
        config([
            'services.telegram.admin.token' => 'admin-token',
            'services.telegram.admin.reports_chat_id' => '777',
        ]);

        $service = TelegramService::adminForReports();

        $this->assertInstanceOf(TelegramService::class, $service);
        $this->assertSame('777', $service->chatId);
    }
}
