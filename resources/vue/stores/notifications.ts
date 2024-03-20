import {defineStore} from "pinia";
import {Notification} from "../../types/Notification";
import API from "../../js/api/api";

export const useNotificationsStore = defineStore('notifications', {
    // @ts-ignore
    persist: true,
    state: () => ({
        notifications: [] as Notification[],
        count: 0,
        loading: false,
        error: null as null | unknown,
        refreshed: null as null | number,
    }),
    getters: {},
    actions: {
        async fetchNotifications(): Promise<void> {
            this.loading = true;
            try {
                this.notifications = await API.request('/notifications')
                    .then((response: any) => response.json())
                    .then((data: any) => data.data);
                this.refreshed = new Date().getTime();
            } catch (error) {
                this.error = error;
            } finally {
                this.loading = false;
            }
        },
        async fetchCount(): Promise<void> {
            try {
                this.count = await API.request("/notifications/unread/count", "GET", {}, true)
                    .then((response: any) => response.json())
                    .then((data: any) => data.data);
            } catch (error) {
                this.error = error;
                this.count = 0;
            }
        },
        async toggleAllRead(): Promise<boolean> {
            try {
                return await API.request("/notifications/read/all", "PUT")
                    .then(() => {
                        this.notifications.map((notification: Notification) => {
                            notification.readAt = new Date().toISOString();
                            return notification
                        });
                        this.count = 0;
                        return true;
                    });
            } catch (error) {
                this.error = error;
                return false;
            }
        },
        async toggleRead(notification: Notification, key: number): Promise<void> {
            let readAction = notification.readAt ? "unread" : "read";
            try {
                await API.request(`/notifications/${readAction}/${notification.id}`, "PUT")
                    .then((response: any) => response.json())
                    .then((data: any) => {
                        this.notifications[key].readAt = data.data.readAt;
                        this.count = readAction === "read" ? this.count - 1 : this.count + 1;
                    });
            } catch (error) {
                this.error = error;
            }
        }
    }
});
