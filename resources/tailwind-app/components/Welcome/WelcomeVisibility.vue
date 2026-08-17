<script setup lang="ts">
import { computed } from 'vue';
import { VISIBILITY_ICONS } from '../../helpers/visibility';

const props = defineProps<{
    label: string;
    levels: string;
}>();

type Level = { value: number; label: string; detail: string };

const levels = computed<Level[]>(() => {
    try {
        return JSON.parse(props.levels) as Level[];
    } catch {
        return [];
    }
});
</script>

<template>
    <div inert role="img" :aria-label="label" class="card bg-base-100 shadow-sm select-none">
        <ul class="card-body p-4 gap-3">
            <li v-for="level in levels" :key="level.value" class="flex items-start gap-3">
                <component :is="VISIBILITY_ICONS[level.value]" class="size-5 shrink-0 mt-0.5 text-primary" />
                <div class="min-w-0">
                    <p class="font-medium leading-tight">{{ level.label }}</p>
                    <p class="text-sm text-base-content/60">{{ level.detail }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>
