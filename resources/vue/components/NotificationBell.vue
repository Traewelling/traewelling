<script setup>
import ModalComponent from "./ModalComponent.vue";
import {onMounted, reactive, ref} from "vue";
import NotificationList from "./NotificationList.vue";

defineProps({
    label: {
        type: String,
        default: "aria-label placeholder",
    },
    link: {
        type: Boolean,
        default: false
    },
    i18nEmpty: {
        type: String
    },
    i18nTitle: {
        type: String
    }
});

onMounted(() => {
    fetchCount();
    setInterval(fetchCount, 30000);
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
    API.request('/notifications/unread/count')
        .then(function (request) {
            request.json().then(function (json) {
                state.count = json.data;
            });
        });
}
</script>

<template>
    <a @click="showModal" class="notifications-board-toggle"
       :class="{'nav-link': link, 'navbar-toggler': !link}" type="button"
       aria-expanded="false"
       :aria-label="label">
        <span class="notifications-bell far fa-bell"></span>
        <span class="notifications-pill badge rounded-pill badge-notification" v-show="state.count">{{
                state.count
            }}</span>
    </a>
    <ModalComponent
        :title="i18nTitle"
        ref="thisModal"
        dialogClass="modal-lg modal-dialog-scrollable"
        bodyClass="p-0"
        :hide-footer="true">
        <template #body class="p-0">
            <NotificationList
                ref="notifications"
                @toggle-read="fetchCount"
                :empty-text="i18nEmpty"
            />
        </template>
        <template #header-extra>
            <a
                href="#"
                class="text-muted"
                @click="$refs.notifications.toggleAllRead"
                v-show="state.count"
                aria-label="{{ __('notifications.mark-all-read') }}">
                <span aria-hidden="true"><i class="fa-solid fa-check-double"></i></span>
            </a>
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

</style>
