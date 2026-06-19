<script setup lang="ts">
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { metersToDistance, SplitDistance } from '../../../vue/helpers/DistanceHelper';

const props = defineProps({
    distance: {
        type: Number,
        required: true,
    },
    class: {
        type: String,
        default: '',
    },
});

const split = ref<SplitDistance>(metersToDistance(props.distance));
</script>

<template>
    <!-- eslint-disable-next-line vue/valid-v-bind -->
    <span v-if="split" :class>
        <span v-if="split.gigameters > 0" class="tooltip" :data-tip="trans('distance.gigameters')">
            {{ split.gigameters }}
            <small class="opacity-65 me-1">
                {{ trans('distance.gigameters.short') }}
            </small>
        </span>
        <span v-if="split.megameters > 0" class="tooltip" :data-tip="trans('distance.megameters')">
            {{ split.megameters }}
            <small class="opacity-65 me-1">
                {{ trans('distance.megameters.short') }}
            </small>
        </span>
        <span v-if="split.kilometers > 0" class="tooltip" :data-tip="trans('distance.kilometers')">
            {{ split.kilometers }}
            <small class="opacity-65 me-1">
                {{ trans('distance.kilometers.short') }}
            </small>
        </span>
        <span
            v-if="!(split.gigameters || split.megameters || split.kilometers)"
            class="tooltip"
            :data-tip="trans('distance.meters')"
        >
            {{ split.meters }}
            <small class="opacity-65">
                {{ trans('distance.meters.short') }}
            </small>
        </span>
    </span>
</template>
