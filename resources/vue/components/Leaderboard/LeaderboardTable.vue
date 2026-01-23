<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { defineProps } from 'vue';
import { LeaderboardUserResource } from '../../../types/Api.gen';
import DurationSpan from '../Status/Partials/DurationSpan.vue';

defineProps({
    data: {
        type: Array as () => LeaderboardUserResource[],
        required: true,
    },
    describedBy: {
        type: String,
        default: '',
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
</script>

<template>
    <table class="table table-striped table-hover" :aria-describedby="describedBy">
        <thead>
            <tr>
                <th scope="col">{{ trans('leaderboard.rank') }}</th>
                <th scope="col">{{ trans('leaderboard.user') }}</th>
                <th scope="col">{{ trans('leaderboard.duration') }}</th>
                <th scope="col">{{ trans('leaderboard.distance') }}</th>
                <th scope="col">{{ trans('leaderboard.averagespeed') }}</th>
                <th scope="col">{{ trans('leaderboard.points') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(row, number) in data" :key="row.user.id">
                <td>{{ number + 1 }}</td>
                <td>
                    <a :href="`@${row.user.username}`">
                        {{ row.user.username }}
                    </a>
                </td>
                <td>
                    <DurationSpan :duration="60 * row.totalDuration" />
                </td>
                <td>{{ Math.round(row.totalDistance / 1000) }} <small class="text-muted">km</small></td>
                <td>
                    {{ calculateAverageSpeed(row.totalDistance, row.totalDuration) }}
                    <small class="text-muted">km/h</small>
                </td>
                <td>{{ row.points || 0 }}</td>
            </tr>
        </tbody>
    </table>
</template>
