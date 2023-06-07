<script>

import NotificationEntry from "./NotificationEntry.vue";

export default {
    components: {NotificationEntry},
    data() {
        return {
            notifications: [],
            loading: true
        }
    },
    props: {
        emptyText: String
    },
    methods: {
        toggleRead(data, key) {
            let readAction = data.readAt ? 'unread' : 'read';
            API.request(`/notifications/${readAction}/${data.id}`, 'PUT')
                .then(async (response) => {
                    const data              = await response.json();
                    this.notifications[key] = data.data;
                })
        }
    },
    mounted() {
        this.loading = true;
        API.request('/notifications')
            .then(async (response) => {
                const data         = await response.json();
                this.notifications = data.data;
                this.loading = false;
            });
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
    <div id="notifications-empty" class="text-center text-muted" v-else>
        <i class="fa-solid fa-envelope fs-1"></i>
        <p class="fs-5">{{ emptyText }}</p>
    </div>
</template>

<style scoped>

</style>
