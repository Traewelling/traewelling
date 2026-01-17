<script>
import { Modal } from 'bootstrap';
import { trans } from 'laravel-vue-i18n';

export default {
    props: {
        title: {
            type: String,
            default: '<<Title goes here>>',
        },
        bodyClass: {
            type: String,
            default: null,
        },
        headerClass: {
            type: String,
            default: 'text-dark',
        },
        dialogClass: {
            type: String,
            default: null,
        },
        hideFooter: {
            type: Boolean,
            default: false,
        },
        hideBody: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            modalObj: null,
        };
    },
    mounted() {
        this.modalObj = new Modal(this.$refs.modalComponent);
    },
    methods: {
        trans,
        show() {
            this.modalObj.show();
        },
        hide() {
            this.modalObj.hide();
        },
    },
};
</script>

<template>
    <div ref="modalComponent" class="modal fade" tabindex="-1" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog" :class="dialogClass">
            <div class="modal-content">
                <div class="modal-header" :class="headerClass">
                    <h5 class="modal-title" :class="{ 'flex-grow-1': !!$slots['header-extra'] }">
                        {{ title }}
                    </h5>
                    <slot name="header-extra" />
                    <button type="button" class="btn-close" aria-label="Close" @click="hide" />
                </div>
                <div v-if="!hideBody" class="modal-body" :class="bodyClass">
                    <slot name="body" />
                </div>
                <div v-if="!hideFooter" class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="hide()">
                        {{ trans('menu.close') }}
                    </button>
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </div>
</template>
