<script>
import Modal from "bootstrap/js/dist/modal";
import {trans} from "laravel-vue-i18n";

export default {
    name: "FullScreenModal",
    props: {
        title: String,
        bodyClass: String,
    },
    data() {
        return {
            modal: null,
        };
    },
    methods: {
        trans,
        mount() {
            this.modal = new Modal(this.$refs.myModal, {})
        },
        show() {
            this.modal.show();
        },
        hide() {
            this.modal.hide();
        }
    },
    mounted() {
        this.mount()
    }
}
</script>

<template>
    <div class="modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" ref="myModal">
        <div class="modal-dialog modal-lg modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header d-flex">
                    <slot name="header"></slot>
                    <slot name="close">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </slot>
                </div>
                <div class="modal-body" :class="bodyClass">
                    <slot name="body"></slot>
                </div>
                <div class="modal-footer d-none">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ trans("menu.close") }}
                    </button>
                    <button type="button" class="btn btn-primary">
                        {{ trans("modals.edit-confirm") }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
