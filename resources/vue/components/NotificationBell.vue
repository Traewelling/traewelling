<script>
import ModalComponent from './ModalComponent.vue';
import NotificationList from './NotificationList.vue';
import { useNotificationsStore } from '../stores/notifications';

export default {
    components: {
        ModalComponent,
        NotificationList,
    },
    props: {
        link: {
            type: Boolean,
            default: false,
        },
        allowFetch: {
            type: Boolean,
            default: true,
        },
    },
    setup() {
        const state = useNotificationsStore();

        return { state };
    },
    data() {
        return {
            fetchInterval: null,
        };
    },
    computed: {
        count() {
            return this.state.count < 0 ? 0 : this.state.count;
        },
    },
    mounted() {
        if (this.allowFetch) {
            this.fetchCount();
            this.fetchInterval = setInterval(this.fetchCount, 30000);
        }
    },
    beforeUnmount() {
        if (this.fetchInterval) {
            clearInterval(this.fetchInterval);
        }
    },
    methods: {
        showModal() {
            this.state.fetchNotifications();
            this.$refs.thisModal.show();
        },
        fetchCount() {
            this.state.fetchCount();
        },
    },
};
</script>

<template>
    <button
        class="btn btn-link btn-transparent text-white notifications-board-toggle"
        :class="{ 'nav-link': link, 'navbar-toggler': !link }"
        type="button"
        aria-expanded="false"
        :aria-label="$t('notifications.show')"
        @click="showModal"
    >
        <span class="notifications-bell fa-bell" :class="{ fas: !!count, far: !count }" />
        <span v-show="count" class="notifications-pill badge rounded-pill badge-notification">
            {{ count }}
        </span>
    </button>
    <ModalComponent
        ref="thisModal"
        :title="$t('notifications.title')"
        dialog-class="modal-lg modal-dialog-scrollable"
        body-class="p-0"
        :hide-footer="true"
    >
        <template #body class="p-0">
            <NotificationList ref="notifications" />
        </template>
        <template #header-extra>
            <button
                v-show="state.count"
                type="button"
                class="btn btn-sm btn-link py-0 px-1 fs-5 text-muted"
                :aria-label="$t('notifications.mark-all-read')"
                @click="$refs.notifications.toggleAllRead"
            >
                <span aria-hidden="true"><i class="fa-solid fa-check-double" /></span>
            </button>
        </template>
    </ModalComponent>
</template>

<style scoped lang="scss">
@import '../../sass/variables';

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

.badge {
    padding: 0.35rem 0.65rem !important;
}
</style>
