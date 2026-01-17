<script setup lang="ts">
import { PropType, ref, watch } from 'vue';
import { StopoverResource } from '../../../../types/Api.gen';
import { trans } from 'laravel-vue-i18n';
import { getArrivalForStopover } from '../../../helpers/DateTimeHelper';
import { DateTime } from 'luxon';

const props = defineProps({
    stopovers: {
        type: Array as PropType<StopoverResource[]>,
        default: [],
    },
    inProgress: {
        type: Boolean,
        default: false,
    },
});

const nextStop = ref(null as StopoverResource | null);

function getNextStop() {
    if (!props.inProgress) {
        nextStop.value = null;
        return;
    }

    props.stopovers.every(stopover => {
        const diff = getArrivalForStopover(stopover).dateTime.diffNow('seconds');
        if (diff.seconds > 0) {
            nextStop.value = stopover;
            return false;
        }

        return true;
    });
}

watch(
    () => props.stopovers,
    () => {
        getNextStop();
    },
    { immediate: true },
);
watch(
    () => props.inProgress,
    () => {
        if (props.inProgress) {
            getNextStop();
            setInterval(getNextStop, 10000);
        } else {
            nextStop.value = null;
        }
    },
    { immediate: true },
);

getNextStop();
if (props.inProgress) {
    setInterval(getNextStop, 10000);
}
</script>

<template>
    <li v-if="nextStop">
        <p class="text-muted font-italic mt-2">
            {{ trans('stationboard.next-stop') }}

            <a :href="`/stationboard?stationId=${nextStop.id}&stationName=${nextStop.name}`" class="text-trwl clearfix">
                {{ nextStop.name }}
                ({{ getArrivalForStopover(nextStop).toLocaleString(DateTime.TIME_SIMPLE) }})
            </a>
        </p>
    </li>
</template>
