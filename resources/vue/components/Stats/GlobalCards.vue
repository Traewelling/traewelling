<script setup lang="ts">
import { PropType } from 'vue';
import DurationSpan from '../Status/Partials/DurationSpan.vue';
import { StatisticsGlobalData } from '../../../types/Api.gen';
import { trans } from 'laravel-vue-i18n';

defineProps({
    stats: {
        type: Object as PropType<StatisticsGlobalData>,
        required: true,
    },
    from: {
        type: Object as PropType<Date>,
        required: true,
    },
    until: {
        type: Object as PropType<Date>,
        required: true,
    },
});

</script>

<template>
    <div class="global-cards row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-ruler fa-4x mt-1" aria-hidden="true" />
                    <div>
                        <span class="font-weight-bold fs-2">
                            {{ (stats.distance / 1000).toFixed(0) }} <small class="text-muted">km</small>
                        </span>
                        <br>
                        <small class="text-muted">{{ trans('stats.global.distance') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-4x mt-1" aria-hidden="true" />
                    <div>
                        <DurationSpan class="font-weight-bold fs-2" :duration="stats.duration" />
                        <br>
                        <small class="text-muted">{{ trans('stats.global.duration') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-4x mt-1" aria-hidden="true" />
                    <div>
                        <span class="font-weight-bold fs-2">{{ stats.user_count }}x</span>
                        <br>
                        <small class="text-muted">{{ trans('stats.global.active') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 text-center">
            <small class="text-muted">*{{ trans('stats.global.explain', { fromDate: from.toLocaleDateString(), toDate: until.toLocaleDateString() }) }}</small>
        </div>
    </div>
</template>
