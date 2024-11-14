<script>
import ModalComponent from "./ModalComponent.vue";
import NotificationList from "./NotificationList.vue";
import {useNotificationsStore} from "../stores/notifications";

export default {
    setup() {
        const state = useNotificationsStore();

        return {state};
    },
    props: {
        link: {
            type: Boolean,
            default: false
        },
        allowFetch: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            fetchInterval: null,
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
    mounted() {
        if (this.allowFetch) {
            this.fetchCount();
            this.fetchInterval = setInterval(this.fetchCount, 30000);
        }
    },
    beforeDestroy() {
        if (this.fetchInterval) {
            clearInterval(this.fetchInterval);
        }
    },
    components: {
        ModalComponent,
        NotificationList
    },
    computed: {
        count() {
            return this.state.count < 0 ? 0 : this.state.count;
        }
    }
}
</script>

<template>
    <button @click="showModal" class="btn btn-link btn-transparent text-white notifications-board-toggle"
            :class="{'nav-link': link, 'navbar-toggler': !link}" type="button"
            aria-expanded="false"
            :aria-label="$t('notifications.show')">
        <span class="notifications-bell fa-bell" :class="{'fas': !!count,  'far': !count}"></span>
        <span class="notifications-pill badge rounded-pill badge-notification" v-show="count">
              {{ count }}
        </span>
    </button>
    <ModalComponent
        :title="$t('notifications.title')"
        ref="thisModal"
        dialogClass="modal-lg modal-dialog-scrollable"
        bodyClass="p-0"
        :hide-footer="true">
        <template #body class="p-0">
            <NotificationList ref="notifications"/>
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
