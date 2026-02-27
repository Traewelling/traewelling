<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next';
defineProps<{
    title: string;
    description?: string;
    badge?: string;
    badgeClass?: string;
}>();
</script>

<template>
    <li class="list-row cursor-pointer hover:bg-base-200">
        <div class="list-col-grow">
            <div class="font-semibold">{{ title }}</div>
            <div class="text-xs opacity-60" v-if="description">{{ description }}</div>
            <div class="text-xs opacity-60" v-if="$slots.default">
                <slot></slot>
            </div>
        </div>
        <div class="flex items-center" v-if="badge">
            <div class="badge badge-xs badge-neutral badge-outline text-xs" :class="badgeClass">
                {{ badge }}
            </div>
        </div>
        <button class="btn btn-square btn-ghost">
            <ChevronRight class="size-6" />
        </button>
    </li>
</template>
