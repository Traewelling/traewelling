<script setup lang="ts">
import { Clock, Route, Users } from '@lucide/vue';

defineProps<{
    distance: string;
    distanceLabel: string;
    users: string;
    usersLabel: string;
    duration: string;
    durationLabel: string;
}>();

const accent = 'text-primary dark:text-red-400';
</script>

<template>
    <div class="stats flex flex-col lg:flex-row bg-base-100 shadow-lg">
        <div class="stat">
            <div class="stat-figure" :class="accent">
                <Route class="inline-block size-8" />
            </div>
            <div class="stat-value" :class="accent">{{ distance }}</div>
            <div class="stat-desc">{{ distanceLabel }}</div>
        </div>

        <div class="stat">
            <div class="stat-figure text-secondary">
                <Users class="inline-block size-8" />
            </div>
            <div class="stat-value text-secondary">{{ users }}</div>
            <div class="stat-desc">{{ usersLabel }}</div>
        </div>

        <div class="stat">
            <div class="stat-figure" :class="accent">
                <Clock class="inline-block size-8" />
            </div>
            <div class="stat-value" :class="accent">{{ duration }}</div>
            <div class="stat-desc">{{ durationLabel }}</div>
        </div>
    </div>
</template>
