<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Api, WebhookResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const emits = defineEmits(['error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const webhooks = ref<WebhookResource[]>([]);

function removeWebhook(webhookId: number) {
    api.webhooks
        .deleteWebhook(webhookId)
        .then(() => {
            webhooks.value = webhooks.value.filter((t) => t.id !== webhookId);
        })
        .catch((error) => {
            emits('error', error.error.message);
        });
}

function fetchWebhooks() {
    api.webhooks
        .getWebhooks()
        .then((response) => {
            response.json().then((data) => {
                webhooks.value = data;
            });
        })
        .catch((error) => {
            emits('error', error.error.message);
        });
}

function openModal() {
    fetchWebhooks();
    modal.value?.showModal();
}
</script>

<template>
    <SettingsListRow
        :title="trans('settings.title-webhooks')"
        :description="trans('settings.webhooks.description')"
        @click.prevent="openModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold mb-4">{{ trans('settings.title-webhooks') }}</h3>
            <span>
                {{ trans('settings.webhook-description') }}
            </span>
            <p v-if="webhooks.length === 0" class="text-error">
                {{ trans('settings.no-webhooks') }}
            </p>
            <ul v-if="webhooks.length > 0" class="list">
                <!-- Render grouped tokens by client -->
                <li v-for="webhook in webhooks" :key="webhook.id" class="list-row">
                    <div class="list-col-grow">
                        <h6 class="mb-0">
                            {{ webhook.client.name }}
                        </h6>
                        <p class="mb-0 opacity-75">
                            {{ trans('settings.webhook-event-notifications-description') }}:
                            <span
                                v-for="wEvent in webhook.events"
                                :key="wEvent.type"
                                class="badge badge-sm badge-primary me-1"
                            >
                                {{ wEvent.type }}
                            </span>
                        </p>
                        <p class="mb-0 opacity-75">
                            {{ trans('settings.created') }}
                            {{ new Date(webhook.createdAt).toLocaleString() }}
                        </p>
                    </div>
                    <button role="button" class="btn btn-sm btn-error" @click="removeWebhook(webhook.id)">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </li>
            </ul>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.close') }}</button>
                </form>
            </div>
        </div>
    </dialog>
</template>
