<script setup lang="ts">
import GitHub from '../Icons/GitHub.vue';
import Mastodon from '../Icons/Mastodon.vue';

const links = [
    { name: 'Mastodon', href: 'https://chaos.social/@Traewelling', icon: Mastodon, rel: 'noopener noreferrer me' },
    { name: 'GitHub', href: 'https://github.com/traewelling/traewelling', icon: GitHub, rel: 'noopener noreferrer' },
];
</script>

<template>
    <div class="grid grid-flow-col gap-4">
        <a
            v-for="link in links"
            :key="link.name"
            :href="link.href"
            :rel="link.rel"
            :aria-label="link.name"
            target="_blank"
            class="fill-current stroke-none"
        >
            <component :is="link.icon" class="inline-block size-8" />
        </a>
    </div>
</template>
