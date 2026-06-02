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
    <div role="alert" class="alert alert-vertical sm:alert-horizontal">
        <button type="button" class="btn btn-outline btn-danger btn-sm" @click="emit('openDelete')">
            {{ trans('settings.delete-account.more') }}
        </button>
        <button type="button" class="btn btn-success btn-sm" :disabled="loadingAccept" @click="emit('accept')">
            {{ trans('privacy.sign.more') }}
        </button>
    </div>
</template>
