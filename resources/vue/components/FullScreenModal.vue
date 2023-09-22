<script>
import Modal from "bootstrap/js/dist/modal";

export default {
    name: "FullScreenModal",
    props: {
        title: String,
    },
    data() {
        return {
            modal: null,
        };
    },
    methods: {
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <slot name="body"></slot>
                </div>
                <div class="modal-footer d-none">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
