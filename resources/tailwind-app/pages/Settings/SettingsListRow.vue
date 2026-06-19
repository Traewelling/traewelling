<script setup lang="ts">
import { ChevronRight, TriangleAlert } from '@lucide/vue';

defineProps<{
    title: string;
    description?: string;
    badge?: string;
    badgeClass?: string;
    warning?: boolean;
}>();
</script>

<template>
    <li class="list-row items-center cursor-pointer hover:bg-base-200">
        <div class="list-col-grow">
            <div class="font-semibold flex items-center gap-2">
                {{ title }}
                <TriangleAlert v-if="warning" class="size-4 text-warning" />
            </div>
            <div v-if="description" class="text-xs" :class="warning ? 'text-warning font-medium' : 'opacity-60'">
                {{ description }}
            </div>
            <div v-if="$slots.default" class="text-xs opacity-60">
                <slot></slot>
            </div>
        </div>
        <div v-if="badge" class="flex items-center">
            <div class="badge badge-xs badge-outline text-xs" :class="badgeClass">
                {{ badge }}
            </div>
        </div>
        <button class="btn btn-square btn-ghost">
            <ChevronRight class="size-6" />
        </button>
    </li>
</template>
