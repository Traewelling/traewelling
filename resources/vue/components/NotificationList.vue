<script>
import API from "../../js/api/api";
import NotificationEntry from "./NotificationEntry.vue";

export default {
    components: {NotificationEntry},
    emits: ["toggle-read"],
    data() {
        return {
            notifications: [],
            loading: true
        }
    },
    methods: {
        toggleRead(data, key) {
            let readAction = data.readAt ? "unread" : "read";
            API.request(`/notifications/${readAction}/${data.id}`, "PUT")
                .then(async (response) => {
                    const data              = await response.json();
                    this.notifications[key] = data.data;
                    this.$emit("toggle-read");
                });
        },
        toggleAllRead() {
            return API.request("/notifications/read/all", "PUT")
                .then(API.handleDefaultResponse)
                .then(() => {
                    this.notifications.map((notification) => {
                        notification.readAt = new Date().toISOString();
                        return notification
                    });
                    this.$emit("toggle-read");
                });
        },
        fetchNotifications() {
            this.loading = true;
            API.request("/notifications")
                .then(async (response) => {
                    const data         = await response.json();
                this.notifications = data.data;
                this.loading = false;
            }).catch(() => {
                this.loading = false;
                this.notifications = null;
            });
        }
    },
    mounted() {
        this.fetchNotifications();
    }
}
</script>

<template>
    <div id="notifications-loading" class="text-center text-muted" v-if="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <div id="notifications-list" v-else-if="notifications.length">
        <NotificationEntry
            v-for="(item, index) in notifications"
            v-bind="item"
            :key="item.id"
            @toggleRead="toggleRead(item, index)">
        </NotificationEntry>
    </div>
    <div class="text-center text-muted notifications-empty" v-else>
        <i class="fa-solid fa-envelope fs-1"></i>
        <p class="fs-5">{{ $t("notifications.empty") }}</p>
    </div>
</template>

<style scoped lang="scss">

@import "../../sass/variables";

.row {
    background-color: white;
    padding: 1rem 0;
    border-bottom: 0.5rem solid $body-bg;
    margin: 0;
}

.notifications-empty {
    padding: 2rem 0;
}

.dark {
    .row {
        background-color: $dm-base-5;
        border-bottom-color: $dm-base-5;
    }
}
</style>
