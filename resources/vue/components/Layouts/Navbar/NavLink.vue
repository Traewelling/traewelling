<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    href: {
        type: String,
        required: false,
        default: '#',
    },
});

const isActive = computed(() => {
    return window.location.pathname.startsWith(props.href);
});
</script>

<template>
    <a class="nav-link" :class="{ active: isActive }" :href>
        <slot />
    </a>
</template>
