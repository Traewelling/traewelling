<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Http;
use Tests\Unit\UnitTestCase;

class AdminNotificationServiceTest extends UnitTestCase
{
    public function test_send_event_notification_sends_both_channels_and_returns_ids(): void
    {
        config([
            'services.telegram.admin.token' => 'tg-token',
            'services.telegram.admin.events_chat_id' => '123',
            'services.matrix.admin.homeserver' => 'https://matrix.example.org',
            'services.matrix.admin.token' => 'mat-token',
            'services.matrix.admin.events_room_id' => '!room:example.org',
        ]);
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 42]], 200),
            'https://matrix.example.org/*' => Http::response(['event_id' => '$mat123'], 200),
        ]);

        $result = AdminNotificationService::sendEventNotification('<b>Hello</b>');

        $this->assertSame(42, $result->telegramId);
        $this->assertSame('$mat123', $result->matrixId);
        $this->assertTrue($result->hasAny());
    }

    public function test_send_report_notification_skips_when_nothing_configured(): void
    {
        config([
            'services.telegram.admin.token' => null,
            'services.matrix.admin.homeserver' => null,
            'services.matrix.admin.token' => null,
        ]);
        Http::fake();

        $result = AdminNotificationService::sendReportNotification('<b>Report</b>');

        $this->assertNull($result->telegramId);
        $this->assertNull($result->matrixId);
        $this->assertFalse($result->hasAny());
        Http::assertNothingSent();
    }

    public function test_send_notification_continues_and_returns_null_when_both_channels_fail(): void
    {
        config([
            'services.telegram.admin.token' => 'tg-token',
            'services.telegram.admin.events_chat_id' => '123',
            'services.matrix.admin.homeserver' => 'https://matrix.example.org',
            'services.matrix.admin.token' => 'mat-token',
            'services.matrix.admin.events_room_id' => '!room:example.org',
        ]);
        Http::fake([
            'https://api.telegram.org/*' => Http::response('Forbidden', 403),
            'https://matrix.example.org/*' => Http::response('Forbidden', 403),
        ]);

        $result = AdminNotificationService::sendEventNotification('<b>Hello</b>');

        $this->assertNull($result->telegramId);
        $this->assertNull($result->matrixId);
    }

    public function test_delete_report_notification_calls_both_channels(): void
    {
        config([
            'services.telegram.admin.token' => 'tg-token',
            'services.telegram.admin.reports_chat_id' => '123',
            'services.matrix.admin.homeserver' => 'https://matrix.example.org',
            'services.matrix.admin.token' => 'mat-token',
            'services.matrix.admin.reports_room_id' => '!room:example.org',
        ]);
        Http::fake(['*' => Http::response(['ok' => true, 'event_id' => '$red'], 200)]);

        AdminNotificationService::deleteReportNotification(42, '$mat123');

        Http::assertSentCount(2);
    }

    public function test_delete_event_notification_skips_when_ids_are_null(): void
    {
        Http::fake();

        AdminNotificationService::deleteEventNotification(null, null);

        Http::assertNothingSent();
    }

    public function test_delete_notification_skips_when_services_not_configured(): void
    {
        config([
            'services.telegram.admin.token' => null,
            'services.matrix.admin.homeserver' => null,
        ]);
        Http::fake();

        AdminNotificationService::deleteEventNotification(42, '$mat123');

        Http::assertNothingSent();
    }

    public function test_delete_notification_swallows_matrix_exception(): void
    {
        config([
            'services.telegram.admin.token' => null,
            'services.matrix.admin.homeserver' => 'https://matrix.example.org',
            'services.matrix.admin.token' => 'mat-token',
            'services.matrix.admin.events_room_id' => '!room:example.org',
        ]);
        Http::fake(['https://matrix.example.org/*' => Http::response('Forbidden', 403)]);

        // Should not throw
        AdminNotificationService::deleteEventNotification(null, '$mat123');

        $this->assertTrue(true);
    }
}
