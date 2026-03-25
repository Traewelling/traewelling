<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
</script>

<template>
    <span class="loading loading-spinner loading-sm text-primary"></span>
    <span class="sr-only">{{ trans('menu.loading') }}</span>
</template>
