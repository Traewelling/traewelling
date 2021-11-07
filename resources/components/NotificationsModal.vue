<template>
    <div id="notifications-board" ref="modal" aria-hidden="true" aria-labelledby="notifications-board-title"
         class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="notifications-board-title" class="modal-title">
                        {{ i18n.get('_.notifications.title') }}
                    </h4>
                    <button id="mark-all-read" :aria-label="i18n.get('_.notifications.mark-all-read')" class="close"
                            role="close" type="button" @click="readAll">
                        <span aria-hidden="true"><i aria-hidden="true" class="fas fa-check-double"></i></span>
                    </button>
                    <button aria-label="Close" class="close" type="button" v-on:click="hide">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div v-if="notifications && notifications.length > 0" id="notifications-list" ref="list"
                     class="modal-body">
                    <Notification v-for="data in notifications" v-bind:key="data.id" :data="data" v-on:close="hide"
                                  v-on:decrease="$emit('decrease')" v-on:increase="$emit('increase')"></Notification>
                </div>
                <div v-else id="notifications-empty" class="text-center text-muted">
                    {{ i18n.get('_.notifications.empty') }}
                    <br/>¯\_(ツ)_/¯
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {Modal} from "bootstrap";
import Notification from "./Notification";
import Notifications from "../js/ApiClient/Notifications";

export default {
    name: "NotificationsModal",
    components: {Notification},
    inject: ["notyf"],
    data() {
        return {
            modal: null,
            notifications: null,
        };
    },
    mounted() {
        this.modal = new Modal(this.$refs.modal);
    },
    methods: {
        show() {
            this.fetchNotifications();
            this.modal.show();
        },
        hide() {
            this.modal.hide();
        },
        fetchNotifications() {
            Notifications.fetchNotifications()
                .then((data) => {
                    this.notifications = data;
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        readAll() {
            Notifications.readAll()
                .then((data) => {
                    this.notifications = data;
                    this.$emit("reset");
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
    }
};
</script>

<style scoped>

</style>
