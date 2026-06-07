<script setup lang="ts">
import { Gauge, Gem, Route, Timer } from '@lucide/vue';
import { LeaderboardUserResource } from '../../../../types/Api.gen';
import DurationSpan from '../../../../vue/components/Status/Partials/DurationSpan.vue';

const props = defineProps({
    entry: {
        type: Object as () => LeaderboardUserResource,
        required: true,
    },
    number: {
        type: Number,
        required: true,
    },
    showPoints: {
        type: Boolean,
        required: false,
    },
    showDuration: {
        type: Boolean,
        required: false,
    },
    showDistance: {
        type: Boolean,
        required: false,
    },
    showSpeed: {
        type: Boolean,
        required: false,
    },
});

const calculateAverageSpeed = (distance: number, duration: number): number => {
    if (duration === 0) {
        return 0;
    }
    // distance in meters, duration in minutes
    const hours = duration / 60;
    const km = distance / 1000;
    return Math.round(km / hours);
};

const redirect = () => {
    window.location.href = `/@${props.entry.user.username}`;
};
</script>

<template>
    <li class="list-row cursor-pointer" @click="redirect()">
        <div class="text-md text-center flex items-center font-thin opacity-30 tabular-nums">
            {{ number + 1 }}
        </div>
        <div>
            <img class="size-10 rounded-box" :src="entry.user.profilePicture" />
        </div>
        <div class="list-col-grow items-center flex">
            <div>
                <div class="text-md overflow-clip line-clamp-1">{{ entry.user.displayName }}</div>
                <div class="text-xs opacity-60">@{{ entry.user.username }}</div>
            </div>
        </div>

        <div class="md:flex items-center" :class="{ flex: showDuration, hidden: !showDuration }">
            <div>
                <Timer class="inline-block size-4 me-1">
                    <title>{{ $t('leaderboard.duration') }}</title>
                </Timer>
                <DurationSpan :duration="60 * entry.totalDuration" />
            </div>
        </div>

        <div class="md:flex items-center" :class="{ flex: showDistance, hidden: !showDistance }">
            <div>
                <Route class="inline-block size-4 me-1">
                    <title>{{ $t('leaderboard.distance') }}</title>
                </Route>
                {{ Math.round(entry.totalDistance / 1000) }} <small class="text-muted">km</small>
            </div>
        </div>

        <div class="items-center md:flex" :class="{ flex: showSpeed, hidden: !showSpeed }">
            <div>
                <Gauge class="inline-block size-4 me-1">
                    <title>{{ $t('leaderboard.averagespeed') }}</title>
                </Gauge>
                {{ calculateAverageSpeed(entry.totalDistance, entry.totalDuration) }}
                <small class="text-muted">km/h</small>
            </div>
        </div>

        <div class="md:flex items-center" :class="{ flex: showPoints, hidden: !showPoints }">
            <div>
                <Gem class="inline-block size-4 me-1">
                    <title>{{ $t('leaderboard.points') }}</title>
                </Gem>
                {{ entry.points || 0 }}
            </div>
        </div>
    </li>
</template>
