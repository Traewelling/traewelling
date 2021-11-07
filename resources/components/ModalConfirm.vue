<template>
    <div ref="deleteModal" class="modal fade" role="dialog" tabindex="-1">
        <div :class="{'modal-lg': large, 'modal-xl': extraLarge}" class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ this.$props.titleText }}</h4>
                    <button :aria-label="i18n.get('_.menu.close')" class="close" type="button" v-on:click="abort">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div v-if="bodyText || slotPassed" :class="bodyClass" class="modal-body">
                    <p v-if="bodyText" v-html="this.$props.bodyText"></p>
                    <slot></slot>
                </div>
                <div class="modal-footer">
                    <button v-if="$props.abortText" class="btn btn-light" type="button" v-on:click="abort">
                        {{ this.$props.abortText }}
                    </button>
                    <button v-if="$props.confirmText" :class="confirmButtonColor" class="btn" type="button"
                            v-on:click="confirm">
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
        confirmButtonColor: null,
        bodyText: null,
        large: false,
        extraLarge: false,
        bodyClass: null
    },
    computed: {
        slotPassed() {
            return !!this.$slots.default;
        }
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
