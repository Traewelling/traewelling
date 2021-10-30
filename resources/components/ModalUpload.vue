<template>
    <div ref="deleteModal" class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ this.$props.titleText }}</h4>
                    <button aria-label="Close" class="close" type="button" v-on:click="abort">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <vue-cropper
                        ref="cropper"
                        alt="Source Image"
                        src="http://beta.localhost:8000/profile/Gertrud123/profilepicture?1635624711991"
                        @crop="..."
                        @cropend="..."
                        @cropmove="..."
                        @cropstart="..."
                        @ready="..."
                        @zoom="..."
                    >
                    </vue-cropper>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" v-on:click="abort">{{ this.$props.abortText }}</button>
                    <button :class="confirmButtonColor" class="btn" type="button" v-on:click="confirm">
                        {{ this.$props.confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {Modal} from "bootstrap";
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';

export default {
    name: "ModalUpload",
    components: {VueCropper},
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
        confirmButtonColor: null,
        bodyText: null
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
.my-clipper {
    width: 100%;
    max-width: 700px;
}
</style>
