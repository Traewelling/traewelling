<script setup lang="ts">
import { isLoaded, trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import AppLayout from '../layouts/AppLayout.vue';

const props = defineProps<{
    code: number | string;
    standalone?: boolean;
}>();

const codeStr = computed(() => String(props.code));

const leadKeys: Record<string, string> = {
    '403': 'errors.403.lead',
    '404': 'errors.404.lead',
};

const leadKey = computed(() => leadKeys[codeStr.value] ?? null);
</script>

<template>
    <component :is="standalone ? AppLayout : 'div'">
        <div class="flex flex-col items-center justify-center min-h-[50vh] text-center gap-4 px-4 py-16">
            <template v-if="isLoaded()">
                <p class="text-7xl font-bold text-base-content/10">{{ code }}</p>
                <h1 class="text-2xl font-bold">{{ trans(`error.${codeStr}`) }}</h1>
                <p v-if="leadKey" class="text-base-content/60 max-w-sm">{{ trans(leadKey) }}</p>
                <a href="/" class="btn btn-primary mt-2">{{ trans('errors.actions.home') }}</a>
            </template>
            <template v-else>
                <div class="skeleton h-16 w-24 rounded mx-auto" />
                <div class="skeleton h-7 w-48 rounded mx-auto" />
                <div class="skeleton h-4 w-72 rounded mx-auto" />
                <div class="skeleton h-10 w-36 rounded mx-auto mt-2" />
            </template>
        </div>
    </component>
</template>
