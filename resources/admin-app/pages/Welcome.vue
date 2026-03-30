<script setup lang="ts">
import { Hammer } from 'lucide-vue-next';
import BackendLayout from '../layouts/BackendLayout.vue';
</script>

<template>
    <BackendLayout>
        <div class="flex flex-col items-center justify-center py-24 text-center gap-4">
            <Hammer class="w-16 h-16 text-base-content/20" />
            <h1 class="text-2xl font-bold">Welcome to the Backend</h1>
            <p class="text-base-content/50">Don't do anything stupid.</p>
        </div>
    </BackendLayout>
</template>
