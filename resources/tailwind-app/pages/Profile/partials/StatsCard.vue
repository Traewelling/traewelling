<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Clock, Route, Star } from 'lucide-vue-next';
import { UserResource } from '../../../../types/Api.gen';
import DistanceSpan from '../../../components/Stats/DistanceSpan.vue';
import DurationSpan from '../../../components/Stats/DurationSpan.vue';

defineProps({
    userData: {
        type: Object as () => UserResource,
        required: true,
    },
    showPoints: {
        type: Boolean,
        required: true,
    },
});
</script>

<template>
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4 gap-3">
            <div class="flex items-center gap-3">
                <Route class="w-5 h-5 text-primary shrink-0" />
                <span class="font-semibold text-sm">
                    <DistanceSpan :distance="userData?.totalDistance ?? 0" />
                </span>
            </div>
            <div class="flex items-center gap-3">
                <Clock class="w-5 h-5 text-primary shrink-0" />
                <span class="font-semibold text-sm">
                    <DurationSpan :duration="userData?.totalDuration ?? 0" />
                </span>
            </div>
            <div v-if="showPoints" class="flex items-center gap-3">
                <Star class="w-5 h-5 text-primary shrink-0" />
                <span class="font-semibold text-sm">
                    {{ userData?.points ?? 0 }}
                    <span class="text-base-content/50 ml-0.5">{{ trans('profile.points-abbr') }}</span>
                </span>
            </div>
        </div>
    </div>
</template>
