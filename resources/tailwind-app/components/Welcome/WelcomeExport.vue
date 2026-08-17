<script setup lang="ts">
import { FileJson, FileSpreadsheet, FileText, ShieldCheck } from '@lucide/vue';

defineProps<{
    label: string;
    gdprLabel: string;
}>();

const formats = [
    { name: 'CSV', icon: FileSpreadsheet },
    { name: 'JSON', icon: FileJson },
    { name: 'PDF', icon: FileText },
];
</script>

<template>
    <div inert role="img" :aria-label="label" class="card bg-base-100 shadow-sm select-none">
        <div class="card-body p-4 gap-4">
            <div class="grid grid-cols-3 gap-3">
                <div
                    v-for="format in formats"
                    :key="format.name"
                    class="flex flex-col items-center gap-2 rounded-box border border-base-300 py-5"
                >
                    <component :is="format.icon" class="size-8 text-primary" />
                    <span class="font-medium text-sm">{{ format.name }}</span>
                </div>
            </div>

            <p class="flex items-center gap-2 text-sm text-base-content/60">
                <ShieldCheck class="size-4 shrink-0 text-primary" />
                {{ gdprLabel }}
            </p>
        </div>
    </div>
</template>
