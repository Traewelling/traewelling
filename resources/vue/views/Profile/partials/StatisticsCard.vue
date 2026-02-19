<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Duration } from 'luxon';
import { computed } from 'vue';
import { UserResource } from '../../../../types/Api.gen';
import { useUserStore } from '../../../stores/user';

const authUser = useUserStore();
const props = defineProps<{
    userData: UserResource;
    loadingUser: boolean;
}>();

// Metrics
const kmDisplay = computed(() => {
    const meters = props.userData.trainDistance ?? 0;
    const km = meters / 1000;
    return km.toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1 });
});
const durationParts = computed(() => {
    const minutes = props.userData.trainDuration ?? 0;
    const dur = Duration.fromObject({ minutes }).shiftTo('days', 'hours', 'minutes');
    return { d: dur.days ?? 0, h: dur.hours ?? 0, m: Math.round(dur.minutes ?? 0) };
});
const showPoints = computed(() => !!(props.userData.pointsEnabled || authUser.user?.pointsEnabled));
</script>

<template>
    <div class="card mb-3 shadow-sm rounded">
        <div class="card-body">
            <div class="row text-center gx-2 gy-3">
                <div class="col">
                    <i class="fa fa-route fa-2x text-trwl" />
                    <div class="h5 mb-0">
                        {{ kmDisplay }}
                        <small class="text-muted">km</small>
                    </div>
                </div>
                <div class="col">
                    <i class="fa fa-stopwatch fa-2x text-trwl" />
                    <div class="h5 mb-0">
                        {{ durationParts.d }}<small class="text-muted">d</small>&nbsp; {{ durationParts.h
                        }}<small class="text-muted">h</small>&nbsp; {{ durationParts.m
                        }}<small class="text-muted">min</small>
                    </div>
                </div>
                <div v-if="showPoints" class="col">
                    <i class="fa fa-dice-d20 fa-2x text-trwl" />
                    <div class="h5 mb-0">
                        {{ userData?.points ?? 0 }}
                        <small class="text-muted">{{ trans('profile.points-abbr') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
