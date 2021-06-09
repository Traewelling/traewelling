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
            <span aria-hidden="true"><i class="fas fa-check-double"></i></span>
          </button>
          <button type="button" class="close" data-mdb-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="notifications-list">
          <div id="notifications-empty" class="text-center text-muted">{{ i18n.get('_.notifications.empty') }}
            <br/>¯\_(ツ)_/¯
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {Modal} from "bootstrap";

export default {
  name: "NotificationsModal",
  data() {
    return {
      modal: null,
    };
  },
  mounted() {
    this.modal = new Modal(this.$refs.modal);
  },
  methods: {
    show() {
      this.modal.show();
    },
    hide() {
      this.modal.hide();
    }
  }
};
</script>

<style scoped>

</style>