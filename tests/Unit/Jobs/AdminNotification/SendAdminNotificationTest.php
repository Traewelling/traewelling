<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\AdminNotification;

use App\Jobs\AdminNotification\SendAdminNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\Unit\UnitTestCase;

class SendAdminNotificationTest extends UnitTestCase
{
    public function test_stores_telegram_message_id_on_model_after_send(): void
    {
        config([
            'services.telegram.admin.token' => 'test-token',
            'services.telegram.admin.events_chat_id' => '123456',
        ]);
        Http::fake([
            'https://api.telegram.org/*' => Http::response(
                ['ok' => true, 'result' => ['message_id' => 42]],
                200,
            ),
        ]);

        $model = Mockery::mock(Model::class);
        $model->shouldReceive('update')
            ->once()
            ->with(Mockery::on(fn ($data) => $data['telegram_notification_id'] === 42 && array_key_exists('matrix_notification_id', $data)));

        new SendAdminNotification('events', '<b>Test</b>', $model)->handle();

        Http::assertSentCount(1);
    }

    public function test_skips_model_update_when_no_channels_configured(): void
    {
        config([
            'services.telegram.admin.token' => null,
            'services.telegram.admin.events_chat_id' => null,
        ]);
        Http::fake();

        $model = Mockery::mock(Model::class);
        $model->shouldNotReceive('update');

        new SendAdminNotification('events', '<b>Test</b>', $model)->handle();

        Http::assertNothingSent();
    }

    public function test_handle_reports_type_routes_to_report_notification(): void
    {
        config(['services.telegram.admin.token' => null, 'services.matrix.admin.homeserver' => null]);
        Http::fake();

        $model = Mockery::mock(Model::class);
        $model->shouldNotReceive('update');

        new SendAdminNotification('reports', '<b>Report</b>', $model)->handle();

        Http::assertNothingSent();
    }
}
