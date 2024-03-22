<script>
import NotificationEntry from "./NotificationEntry.vue";
import {useNotificationsStore} from "../stores/notifications";

export default {
    setup() {
        const store = useNotificationsStore();

        return { store };
    },
    components: {NotificationEntry},
    methods: {
        toggleAllRead() {
            this.store.toggleAllRead().then(() => {
                notyf.success(this.$t("notifications.readAll.success"));
            });
        }
    },
}
</script>

<template>
    <div id="notifications-loading" class="text-center text-muted" v-if="store.loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <div id="notifications-list" v-else-if="store.notifications.length">
        <NotificationEntry
            v-for="(item, index) in store.notifications"
            v-bind="item"
            :key="item.id"
            @toggleRead="store.toggleRead(item, index)">
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
