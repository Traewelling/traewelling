<script setup lang="ts">
import { BellOff, CheckCheck, X } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { useNotificationsStore } from '../../../vue/stores/notifications';
import NotificationEntry from '../../pages/Notifications/partials/NotificationEntry.vue';

const store = useNotificationsStore();
const dialogRef = ref<HTMLDialogElement | null>(null);

function open() {
    store.fetchNotifications();
    dialogRef.value?.showModal();
}

defineExpose({ open });
</script>

<template>
    <dialog ref="dialogRef" class="modal">
        <div
            class="modal-box p-0 max-w-lg w-full flex flex-col h-full max-h-screen sm:max-h-[85vh] rounded-none sm:rounded-box"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-base-200 flex-shrink-0">
                <h2 class="font-semibold text-base">{{ trans('notifications.title') }}</h2>
                <div class="flex items-center gap-1">
                    <button v-if="store.count > 0" class="btn btn-ghost btn-sm gap-1" @click="store.toggleAllRead()">
                        <CheckCheck class="size-4" />
                        <span class="hidden sm:inline">{{ trans('notifications.mark-all-read') }}</span>
                    </button>
                    <form method="dialog">
                        <button class="btn btn-ghost btn-sm btn-square">
                            <X class="size-4" />
                        </button>
                    </form>
                </div>
            </div>

            <!-- Body -->
            <div class="overflow-y-auto flex-1">
                <!-- Loading -->
                <div v-if="store.loading && store.notifications.length === 0" class="flex justify-center py-12">
                    <span class="loading loading-spinner loading-lg" />
                </div>

                <!-- Empty state -->
                <div
                    v-else-if="!store.loading && store.notifications.length === 0"
                    class="flex flex-col items-center justify-center py-12 text-base-content/50"
                >
                    <BellOff class="size-8 mb-2" />
                    <p class="text-sm">{{ trans('notifications.empty') }}</p>
                </div>

                <!-- List -->
                <ul v-else class="list bg-base-100">
                    <NotificationEntry
                        v-for="(notification, index) in store.notifications"
                        :key="notification.id"
                        :notification="notification"
                        @toggle-read="store.toggleRead(notification, index)"
                    />
                </ul>
            </div>
        </div>

        <!-- Backdrop closes modal -->
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>
