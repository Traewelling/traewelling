<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';

defineProps<{
    loadingAccept: boolean;
}>();

const emit = defineEmits<{
    accept: [];
    openDelete: [];
}>();
</script>

<template>
    <div class="d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="emit('openDelete')">
            {{ trans('settings.delete-account.more') }}
        </button>
        <button type="button" class="btn btn-success" :disabled="loadingAccept" @click="emit('accept')">
            {{ trans('privacy.sign.more') }}
        </button>
    </div>
</template>
