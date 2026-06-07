<script setup lang="ts">
import { BellOff, CheckCheck } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { useNotificationsStore } from '../../../vue/stores/notifications';
import AppLayout from '../../layouts/AppLayout.vue';
import NotificationEntry from './partials/NotificationEntry.vue';

const store = useNotificationsStore();
store.fetchNotifications();
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">{{ trans('notifications.title') }}</h1>
                <button v-if="store.count > 0" class="btn btn-sm btn-ghost" @click="store.toggleAllRead()">
                    <CheckCheck class="size-4" />
                    {{ trans('notifications.mark-all-read') }}
                </button>
            </div>

            <div v-if="store.loading && store.notifications.length === 0" class="flex justify-center py-12">
                <span class="loading loading-spinner loading-lg"></span>
            </div>

            <div
                v-else-if="!store.loading && store.notifications.length === 0"
                class="text-center py-12 text-base-content/50"
            >
                <BellOff class="size-8 mx-auto mb-2" />
                <p>{{ trans('notifications.empty') }}</p>
            </div>

            <ul v-else class="list bg-base-100 rounded-box shadow-md">
                <NotificationEntry
                    v-for="(notification, index) in store.notifications"
                    :key="notification.id"
                    :notification="notification"
                    @toggle-read="store.toggleRead(notification, index)"
                />
            </ul>
        </div>
    </AppLayout>
</template>
