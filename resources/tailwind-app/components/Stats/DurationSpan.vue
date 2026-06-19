<script setup lang="ts">
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { minutesToDuration, TimeDuration } from '../../../vue/helpers/DateTimeHelper';

const props = defineProps({
    duration: {
        type: Number,
        required: true,
    },
    class: {
        type: String,
        default: '',
    },
});

const split = ref<TimeDuration>(minutesToDuration(props.duration));
</script>

<template>
    <!-- eslint-disable-next-line vue/valid-v-bind -->
    <span v-if="split" :class>
        <span v-if="split.years && split.years > 0" class="tooltip" :data-tip="trans('time.years')">
            {{ split.years }}
            <small class="opacity-65 me-1">{{ trans('time.years.short') }}</small>
        </span>
        <span v-if="split.days && split.days > 0" class="tooltip" :data-tip="trans('time.days')">
            {{ split.days }}
            <small class="opacity-65 me-1">{{ trans('time.days.short') }}</small>
        </span>
        <span v-if="split.hours && split.hours > 0" class="tooltip" :data-tip="trans('time.hours')">
            {{ split.hours }}
            <small class="opacity-65 me-1">{{ trans('time.hours.short') }}</small>
        </span>
        <span class="tooltip" :data-tip="trans('time.minutes')">
            {{ split.minutes }}
            <small class="opacity-65 tooltip">{{ trans('time.minutes.short') }}</small>
        </span>
    </span>
</template>
