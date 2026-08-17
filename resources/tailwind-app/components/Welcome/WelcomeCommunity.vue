<script setup lang="ts">
import { GitPullRequestArrow, HandCoins, Landmark } from '@lucide/vue';

const props = defineProps<{
    label: string;
    contributorsLabel: string;
    associationLabel: string;
    donationsLabel: string;
    associationUrl: string;
    associationLinkLabel: string;
    supportUrl: string;
    supportLabel: string;
}>();

const points = [
    { icon: GitPullRequestArrow, text: props.contributorsLabel },
    { icon: Landmark, text: props.associationLabel },
    { icon: HandCoins, text: props.donationsLabel },
];
</script>

<template>
    <div :aria-label="label" role="group" class="card bg-base-100 shadow-sm">
        <div class="card-body p-4 gap-4">
            <ul class="flex flex-col gap-3">
                <li v-for="point in points" :key="point.text" class="flex items-start gap-3">
                    <component :is="point.icon" class="size-5 shrink-0 mt-0.5 text-primary" />
                    <span class="min-w-0">{{ point.text }}</span>
                </li>
            </ul>

            <div class="flex flex-wrap gap-2">
                <a :href="associationUrl" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm">
                    {{ associationLinkLabel }}
                </a>
                <a :href="supportUrl" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm">
                    {{ supportLabel }}
                </a>
            </div>
        </div>
    </div>
</template>
