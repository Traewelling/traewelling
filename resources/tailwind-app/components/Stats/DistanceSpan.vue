<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { metersToDistance, SplitDistance } from '../../../vue/helpers/DistanceHelper';

const props = defineProps({
    distance: {
        type: Number,
        required: true,
    },
});

const split = ref<SplitDistance>(metersToDistance(props.distance));
</script>

<template>
    <span v-if="split">
        <span v-if="split.kilometers > 0" class="tooltip cursor-default" :data-tip="`${props.distance} m`">
            {{ split.kilometers }}
            <small class="opacity-65 me-1">
                {{ trans('distance.kilometers.short') }}
            </small>
        </span>
        <span v-if="!split.kilometers" class="tooltip" :data-tip="trans('distance.meters')">
            {{ split.meters }}
            <small class="opacity-65">
                {{ trans('distance.meters.short') }}
            </small>
        </span>
    </span>
</template>
