<template>
  <div class="modal fade" id="notifications-board" tabindex="-1" role="dialog"
       aria-hidden="true" aria-labelledby="notifications-board-title" ref="modal">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="notifications-board-title">
            {{ i18n.get('_.notifications.title') }}
          </h4>
          <button type="button" class="close" id="mark-all-read"
                  :aria-label="i18n.get('_.notifications.mark-all-read')">
            <span aria-hidden="true"><i class="fas fa-check-double" aria-hidden="true"></i></span>
          </button>
          <button type="button" class="close" aria-label="Close" v-on:click="hide">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="notifications-list" ref="list" v-if="notifications && notifications.length > 0">
            <Notification v-for="data in notifications" v-bind:key="data.id" :data="data"></Notification>
        </div>
        <div id="notifications-empty" class="text-center text-muted" v-else>
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
        axios
            .get("/notifications")
            .then((response) => {
                this.notifications = response.data.data;
            })
            .catch((error) => {
                if (error.response) {
                    this.notyf.error(error.response.data.error.message);
                } else {
                    this.notyf.error(this.i18n.get("_.messages.exception.general"));
                }
            });
    }
  }
};
</script>

<style scoped>

</style>
