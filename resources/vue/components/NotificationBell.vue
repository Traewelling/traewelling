<script setup>
import ModalComponent from "./ModalComponent.vue";
import {onMounted, onUnmounted, reactive, ref} from "vue";
import NotificationList from "./NotificationList.vue";
import API from "../../js/api/api";

defineProps({
    link: {
        type: Boolean,
        default: false
    },
});

let fetchInterval = null;

onMounted(() => {
    fetchCount();
    fetchInterval = setInterval(fetchCount, 30000);
});

onUnmounted(() => {
    clearInterval(fetchInterval);
});

let thisModal = ref(null);
let notifications = ref(null);
const state   = reactive({
    count: 0,
})

function showModal() {
    thisModal.value.show();
    notifications.value.fetchNotifications();
}

function fetchCount() {
    API.request("/notifications/unread/count", "GET", {}, true)
        .then(function (request) {
            request.json().then(function (json) {
                state.count = json.data;
            });
        })
        .catch(() => {
            // Do nothing and ignore this error for better ux
        });
}
</script>

<template>
    <button @click="showModal" class="btn btn-link btn-transparent text-white notifications-board-toggle" style=""
       :class="{'nav-link': link, 'navbar-toggler': !link}" type="button"
       aria-expanded="false"
       :aria-label="$t('notifications.show')">
        <span class="notifications-bell fa-bell" :class="{'fas': !!state.count,  'far': !state.count}"></span>
        <span class="notifications-pill badge rounded-pill badge-notification" v-show="state.count">
              {{ state.count }}
        </span>
    </button>
    <ModalComponent
        :title="$t('notifications.title')"
        ref="thisModal"
        dialogClass="modal-lg modal-dialog-scrollable"
        bodyClass="p-0"
        :hide-footer="true">
        <template #body class="p-0">
            <NotificationList
                ref="notifications"
                @toggle-read="fetchCount"
            />
        </template>
        <template #header-extra>
            <button
                type="button"
                class="btn btn-sm btn-link py-0 px-1 fs-5 text-muted"
                @click="$refs.notifications.toggleAllRead"
                v-show="state.count"
                :aria-label="$t('notifications.mark-all-read')">
                <span aria-hidden="true"><i class="fa-solid fa-check-double"></i></span>
            </button>
        </template>
    </ModalComponent>
</template>

<style scoped lang="scss">
@import "../../sass/variables";

.modal-header {
    justify-content: initial;

    h2 {
        flex-grow: 1;
    }

    #mark-all-read {
        text-align: left;
        padding: 0 0.5rem;

        i.fa-solid {
            font-size: 1.2em;
            margin-top: 0.2em;
        }
    }
}

.modal-body {
    padding: 0;
}

.btn-transparent {
    background-color: transparent;
}
</style>
