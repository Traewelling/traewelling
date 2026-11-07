import { defineStore } from 'pinia';
import { ref } from 'vue';
import { Api, Notification } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api' });

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
        const nextCursor = ref<string | null>(null);

        async function loadNotifications(cursor?: string): Promise<void> {
            loading.value = true;
            try {
                const response = await api.notifications.listNotificationsV2({ cursor });
                notifications.value = cursor ? [...notifications.value, ...response.data.data] : response.data.data;
                nextCursor.value = response.data.meta.next_cursor;
                refreshed.value = Date.now();
            } catch (err) {
                error.value = err;
            } finally {
                loading.value = false;
            }
        }

        async function fetchNotifications(): Promise<void> {
            await loadNotifications();
        }

        async function fetchMoreNotifications(): Promise<void> {
            if (nextCursor.value) {
                await loadNotifications(nextCursor.value);
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

        async function deleteNotification(notification: Notification): Promise<void> {
            try {
                await api.notifications.deleteNotification(notification.id);
                notifications.value = notifications.value.filter((entry) => entry.id !== notification.id);
                if (!notification.readAt) {
                    count.value--;
                }
                if (notifications.value.length === 0 && nextCursor.value) {
                    await fetchNotifications();
                }
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
            nextCursor,
            fetchNotifications,
            fetchMoreNotifications,
            fetchCount,
            toggleAllRead,
            toggleRead,
            deleteNotification,
        };
    },
    { persist: true },
);
