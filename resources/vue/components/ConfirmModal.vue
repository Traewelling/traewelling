<script setup lang="ts">
import ModalComponent from './ModalComponent.vue';
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
    confirmButtonClass: {
        type: String,
        default: 'btn btn-danger',
    },
    confirmButtonKey: {
        type: String,
        default: 'modals.delete-confirm',
    },
});

const emit = defineEmits(['confirm']);
const modal = ref<ModalComponent | null>(null);
const show = () => {
    modal.value?.show();
};
const getTitle = () => {
    if (props.title !== '') {
        return trans(props.title);
    }

    return '';
};

const confirm = () => {
    modal.value?.hide();
    emit('confirm');
};

defineExpose({ show });
</script>

<template>
    <ModalComponent ref="modal" :title="getTitle()" :hide-body="true">
        <template #footer>
            <button type="button" :class="confirmButtonClass" @click="confirm()">
                {{ trans(confirmButtonKey) }}
            </button>
        </template>
    </ModalComponent>
</template>
