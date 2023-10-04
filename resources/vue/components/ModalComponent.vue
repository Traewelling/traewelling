<script>
import {Modal} from "bootstrap";

export default {
    props: {
        title: {
            type: String,
            default: "<<Title goes here>>",
        },
        bodyClass: {
            type: String,
            default: null
        },
        headerClass: {
            type: String,
            default: null
        },
        dialogClass: {
            type: String,
            default: null
        },
        hideFooter: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            modalObj: null
        }
    },
    mounted() {
        this.modalObj = new Modal(this.$refs.modalComponent);
    },
    methods: {
        show() {
            this.modalObj.show();
        },
        hide() {
            this.modalObj.hide()
        }
    }
}
</script>

<template>
    <div class="modal fade" tabindex="-1" aria-labelledby=""
         aria-hidden="true" ref="modalComponent">
        <div class="modal-dialog" :class="dialogClass">
            <div class="modal-content">
                <div class="modal-header text-dark" :class="headerClass">
                    <h5 class="modal-title" :class="{'flex-grow-1': !!this.$slots['header-extra']}">
                        {{ title }}
                    </h5>
                    <slot name="header-extra"/>
                    <button type="button" class="btn-close" aria-label="Close" @click="hide"></button>
                </div>
                <div class="modal-body" :class="bodyClass">
                    <slot name="body"/>
                </div>
                <div class="modal-footer" v-if="!hideFooter">
                    <slot name="footer"></slot>
                    <button type="button" class="btn btn-secondary">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
