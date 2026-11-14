<script setup lang="ts">
import { LifeBuoy, Map } from '@lucide/vue';
import GitHub from '../Icons/GitHub.vue';

const props = defineProps<{
    aboutLabel: string;
    activeLabel: string;
    sourceLabel: string;
}>();

const links = [
    { label: props.aboutLabel, href: 'https://help.traewelling.de/features/', icon: LifeBuoy, external: true },
    { label: props.activeLabel, href: '/statuses/active', icon: Map, external: false },
    {
        label: props.sourceLabel,
        href: 'https://github.com/Traewelling/traewelling',
        icon: GitHub,
        iconClass: 'fill-current',
        external: true,
    },
];
</script>

<template>
    <div class="flex flex-wrap gap-x-6 gap-y-2 text-slate-100">
        <a
            v-for="link in links"
            :key="link.href"
            :href="link.href"
            :target="link.external ? '_blank' : undefined"
            :rel="link.external ? 'noopener noreferrer' : undefined"
            class="link link-hover inline-flex items-center gap-1.5"
        >
            <component :is="link.icon" class="size-4" :class="link.iconClass" />
            {{ link.label }}
        </a>
    </div>
</template>
