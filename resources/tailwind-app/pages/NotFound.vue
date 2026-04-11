<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import AppLayout from '../layouts/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <div class="flex flex-col items-center justify-center min-h-[50vh] text-center gap-4">
            <p class="text-6xl font-bold opacity-20">404</p>
            <h1 class="text-2xl font-bold">{{ trans('error.404') }}</h1>
            <p class="opacity-60">{{ trans('errors.404.lead') }}</p>
            <a href="/" class="btn btn-primary">{{ trans('errors.actions.home') }}</a>
        </div>
    </AppLayout>
</template>
