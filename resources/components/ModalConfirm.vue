<template>
  <div class="modal fade" tabindex="-1" role="dialog" ref="deleteModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">{{ this.$props.titleText }}</h4>
          <button type="button" class="close" v-on:click="abort" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" v-on:click="abort">{{ this.$props.abortText }}</button>
          <button type="button" class="btn" :class="confirmButtonColor" v-on:click="confirm">
            {{ this.$props.confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {Modal} from "bootstrap";

export default {
  name: "ModalConfirm",
  data() {
    return {
      modal: null,
    };
  },
  mounted() {
    this.modal = new Modal(this.$refs.deleteModal);
  },
  props: {
    titleText: null,
    abortText: null,
    confirmText: null,
    confirmButtonColor: null
  },
  methods: {
    show() {
      this.modal.show();
    },
    hide() {
      this.modal.hide();
    },
    confirm() {
      this.$emit("confirm");
      this.hide();
    },
    abort() {
      this.$emit("abort");
      this.hide();
    }
  }
};
</script>

<style scoped>

</style>
