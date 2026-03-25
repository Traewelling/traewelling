<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\AdminNotification;

use App\Jobs\AdminNotification\DeleteAdminNotification;
use Illuminate\Support\Facades\Http;
use Tests\Unit\UnitTestCase;

class DeleteAdminNotificationTest extends UnitTestCase
{
    public function test_handle_events_type_calls_delete_event_notification(): void
    {
        config([
            'services.telegram.admin.token' => 'tg-token',
            'services.telegram.admin.events_chat_id' => '123',
        ]);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true], 200)]);

        new DeleteAdminNotification('events', 42, null)->handle();

        Http::assertSentCount(1);
    }

    public function test_handle_reports_type_calls_delete_report_notification(): void
    {
        config([
            'services.telegram.admin.token' => null,
            'services.matrix.admin.homeserver' => null,
        ]);
        Http::fake();

        new DeleteAdminNotification('reports', null, null)->handle();

        Http::assertNothingSent();
    }
}
