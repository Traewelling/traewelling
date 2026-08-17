<script setup lang="ts">
import { computed } from 'vue';
import { VISIBILITY_ICONS } from '../../helpers/visibility';

const props = defineProps<{
    label: string;
    tags: string;
}>();

type Tag = { label: string; value: string; visibility: number };

const tags = computed<Tag[]>(() => {
    try {
        return JSON.parse(props.tags) as Tag[];
    } catch {
        return [];
    }
});
</script>

<template>
    <div inert role="img" :aria-label="label" class="card bg-base-100 shadow-sm select-none">
        <dl class="card-body p-4 gap-0 divide-y divide-base-200">
            <div v-for="tag in tags" :key="tag.label" class="flex items-center gap-3 py-2">
                <dt class="text-sm text-base-content/60 min-w-0 flex-1">{{ tag.label }}</dt>
                <dd class="font-medium tabular-nums">{{ tag.value }}</dd>
                <component :is="VISIBILITY_ICONS[tag.visibility]" class="size-4 shrink-0 text-base-content/30" />
            </div>
        </dl>
    </div>
</template>
