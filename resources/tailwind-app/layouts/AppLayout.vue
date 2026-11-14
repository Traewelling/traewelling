<template>
    <div class="min-h-screen flex flex-col bg-base-300">
        <Navbar />

        <main class="flex-1 w-full" :class="{ 'px-4 sm:px-6 lg:px-8': !legacy, 'max-w-7xl mx-auto py-4': !fullscreen }">
            <slot></slot>
        </main>

        <Footer class="max-md:pb-44" />
        <ActiveStatusCard />
    </div>
</template>

<script setup lang="ts">
import ActiveStatusCard from '../components/ActiveStatusCard.vue';
import Footer from './partials/Footer.vue';
import Navbar from './partials/Navbar.vue';

defineProps({
    legacy: {
        type: Boolean,
        default: false,
    },
    fullscreen: {
        type: Boolean,
        default: false,
    },
});
</script>
