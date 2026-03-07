<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { PropType, ref, watch } from 'vue';
import { StopoverResource } from '../../../../types/Api.gen';
import { getArrivalForStopover, getDepartureForStopover } from '../../../helpers/DateTimeHelper';

const props = defineProps({
    stopovers: {
        type: Array as PropType<StopoverResource[]>,
        default: () => [],
    },
    inProgress: {
        type: Boolean,
        default: false,
    },
});

const nextStop = ref(null as StopoverResource | null);
const isAtStop = ref(false);

function getNextStop() {
    if (!props.inProgress) {
        nextStop.value = null;
        isAtStop.value = false;
        return;
    }

    let found = false;
    props.stopovers.every((stopover) => {
        const departureDiff = getDepartureForStopover(stopover).dateTime.diffNow('seconds');
        if (departureDiff.seconds > 0) {
            nextStop.value = stopover;
            const arrivalDiff = getArrivalForStopover(stopover).dateTime.diffNow('seconds');
            isAtStop.value = arrivalDiff.seconds <= 0;
            found = true;
            return false;
        }

        return true;
    });

    if (!found) {
        nextStop.value = null;
        isAtStop.value = false;
    }
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
            isAtStop.value = false;
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
            {{ isAtStop ? trans('stationboard.current-stop') : trans('stationboard.next-stop') }}

            <a :href="`/stationboard?stationId=${nextStop.id}&stationName=${nextStop.name}`" class="text-trwl clearfix">
                {{ nextStop.name }}
                ({{ getArrivalForStopover(nextStop).toLocaleString(DateTime.TIME_SIMPLE) }})
            </a>
        </p>
    </li>
</template>
