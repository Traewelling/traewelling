<script setup lang="ts">
import { ref } from 'vue';
import { secondsToDuration, TimeDuration } from '../../../helpers/DateTimeHelper';
import { trans } from 'laravel-vue-i18n';

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

const split = ref<TimeDuration>(secondsToDuration(props.duration));
</script>

<template>
    <!-- eslint-disable-next-line vue/valid-v-bind -->
    <span v-if="split" :class>
        <span v-if="split.years && split.years > 0">
            {{ split.years }} <small class="text-muted">{{ trans('time.years.short') }}</small>
        </span>
        <span v-if="split.days && split.days > 0">
            {{ split.days }} <small class="text-muted">{{ trans('time.days.short') }}</small>
        </span>
        <span v-if="split.hours && split.hours > 0">
            {{ split.hours }} <small class="text-muted">{{ trans('time.hours.short') }}</small>
        </span>
        {{ split.minutes }} <small class="text-muted">{{ trans('time.minutes.short') }}</small>
    </span>
</template>
