import { defineStore } from 'pinia';
import { ref } from 'vue';
import { Api, Notification } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

async function markAsRead(id: string) {
    const response = await api.notifications.markAsRead(id);
    return response.data.data;
}

async function markAsUnread(id: string) {
    const response = await api.notifications.markAsUnread(id);
    return response.data.data;
}

export const useNotificationsStore = defineStore(
    'notifications',
    () => {
        const notifications = ref<Notification[]>([]);
        const count = ref<number>(0);
        const loading = ref<boolean>(false);
        const error = ref<unknown | null>(null);
        const refreshed = ref<number | null>(null);

        async function fetchNotifications(): Promise<void> {
            loading.value = true;
            try {
                const response = await api.notifications.listNotifications();
                notifications.value = response.data.data;
                refreshed.value = Date.now();
            } catch (err) {
                error.value = err;
            } finally {
                loading.value = false;
            }
        }

        async function fetchCount(): Promise<void> {
            if (refreshed.value && Date.now() - refreshed.value < 30000) {
                return;
            }
            try {
                const response = await api.notifications.getUnreadCount();
                count.value = response.data.data;
                refreshed.value = new Date().getTime();
            } catch (err) {
                error.value = err;
                count.value = 0;
            }
        }

        async function toggleAllRead(): Promise<boolean> {
            try {
                await api.notifications.markAllAsRead();
                notifications.value = notifications.value.map((notification: Notification) => {
                    notification.readAt = new Date().toISOString();
                    return notification;
                });
                count.value = 0;
                return true;
            } catch (err) {
                error.value = err;
                return false;
            }
        }

        async function toggleRead(notification: Notification, key: number): Promise<void> {
            const readAction = notification.readAt ? 'unread' : 'read';
            try {
                if (notification.readAt) {
                    const data = await markAsUnread(notification.id);
                    notifications.value[key].readAt = data.readAt;
                } else {
                    const data = await markAsRead(notification.id);
                    notifications.value[key].readAt = data.readAt;
                }

                count.value = readAction === 'read' ? count.value - 1 : count.value + 1;
            } catch (err) {
                error.value = err;
            }
        }

        return {
            notifications,
            count,
            loading,
            error,
            refreshed,
            fetchNotifications,
            fetchCount,
            toggleAllRead,
            toggleRead,
        };
    },
    { persist: true },
);
