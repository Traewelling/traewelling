<template>
  <div class="modal fade" tabindex="-1" role="dialog" ref="checkinModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">{{ i18n.get('_.stationboard.new-checkin') }}</h4>
          <button type="button" class="close" v-on:click="hide" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" v-on:click="hide">{{ i18n.get('_.menu.abort') }}</button>
          <button type="button" class="btn btn-primary" v-on:click="hide">
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
import {Modal} from "bootstrap";

<script>
import {Modal} from "bootstrap";

export default {
  name: "CheckInModal",
  data() {
    return {
      modal: null,
      notifications: null,
    };
  },
  mounted() {
    this.modal = new Modal(this.$refs.checkinModal);
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
