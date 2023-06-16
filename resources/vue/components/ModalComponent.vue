<script setup>
import { onMounted, ref } from "vue";
import { Modal } from "bootstrap";
defineProps({
    title: {
        type: String,
        default: "<<Title goes here>>",
    },
    bodyClass: {
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
});
let modalComponent = ref(null);
let thisModalObj = null;

onMounted(() => {
    thisModalObj = new Modal(modalComponent.value);
});
function _show() {
    thisModalObj.show();
}
defineExpose({ show: _show });
</script>

<template>
    <div class="modal fade" tabindex="-1" aria-labelledby=""
         aria-hidden="true" ref="modalComponent">
        <div class="modal-dialog" :class="dialogClass">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" :class="{'flex-grow-1': !!this.$slots['header-extra']}">{{ title }}</h5>
                    <slot name="header-extra" />
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" :class="bodyClass">
                    <slot name="body" />
                </div>
                <div class="modal-footer" v-if="!hideFooter">
                    <slot name="footer"></slot>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
