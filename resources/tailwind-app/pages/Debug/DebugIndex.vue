<script setup lang="ts">
import { Database, MapPin } from '@lucide/vue';
import { RouterLink } from 'vue-router';
import AppLayout from '../../layouts/AppLayout.vue';

const links = [
    { label: 'Stations', to: '/debug/stations', icon: MapPin },
    { label: 'Motis sources', to: '/debug/motis-sources', icon: Database },
];
</script>

<template>
    <AppLayout>
        <div class="flex justify-center">
            <div class="w-full lg:w-2/3">
                <h1 class="text-3xl py-3">Debugging tools</h1>
                <ul class="menu bg-base-100 rounded-box w-full">
                    <li v-for="link in links" :key="link.to">
                        <RouterLink :to="link.to">
                            <component :is="link.icon" class="size-4" />
                            {{ link.label }}
                        </RouterLink>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
