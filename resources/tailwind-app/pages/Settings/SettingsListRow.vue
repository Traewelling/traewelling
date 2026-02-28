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
            <div v-if="description" class="text-xs opacity-60">{{ description }}</div>
            <div v-if="$slots.default" class="text-xs opacity-60">
                <slot></slot>
            </div>
        </div>
        <div v-if="badge" class="flex items-center">
            <div class="badge badge-xs badge-neutral badge-outline text-xs" :class="badgeClass">
                {{ badge }}
            </div>
        </div>
        <button class="btn btn-square btn-ghost">
            <ChevronRight class="size-6" />
        </button>
    </li>
</template>
