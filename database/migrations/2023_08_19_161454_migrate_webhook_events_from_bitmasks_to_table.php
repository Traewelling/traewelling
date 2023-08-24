<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

return new class extends Migration {

    // Copied so when we change WebhookEvent this still works.
    const CHECKIN_CREATE = 1 << 0;
    const CHECKIN_UPDATE = 1 << 1;
    const CHECKIN_DELETE = 1 << 2;
    const NOTIFICATION = 1 << 3;

    public function up(): void {
        DB::table('webhooks')->orderBy('id')->chunkById(100, function (Collection $webhooks) {
            foreach ($webhooks as $webhook) {
                $id = $webhook->id;
                $events_bits = $webhook->events;
                if ($events_bits & self::CHECKIN_CREATE) {
                    self::insertWebhookEvent($id, 'checkin_create');
                }
                if ($events_bits & self::CHECKIN_UPDATE) {
                    self::insertWebhookEvent($id, 'checkin_update');
                }
                if ($events_bits & self::CHECKIN_DELETE) {
                    self::insertWebhookEvent($id, 'checkin_delete');
                }
                if ($events_bits & self::NOTIFICATION) {
                    self::insertWebhookEvent($id, 'notification');
                }
            }
        });
    }

    private static function insertWebhookEvent($webhook_id, $event): void {
        DB::table('webhook_events')->insert([
            'webhook_id' => $webhook_id,
            'event'      => $event
        ]);
    }

    public function down(): void {
        // todo: should this be reversable?
        // possible to mark it as non reversible?
    }
};
