<script setup lang="ts">
import { PropType, ref, watch } from 'vue';
import { StatusResource } from '../../../../types/Api.gen';
import { getArrivalAttribute, timeTypeTooltip } from '../../../helpers/DateTimeHelper';
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';

const props = defineProps({
    status: {
        type: Object as PropType<StatusResource>,
        required: true,
    },
});

const arrival = ref(getArrivalAttribute(props.status));

watch(
    () => props.status,
    () => {
        arrival.value = getArrivalAttribute(props.status);
    },
    {
        immediate: true,
    },
);
</script>

<template>
    <li>
        <i class="trwl-bulletpoint" aria-hidden="true" />
        <span class="text-trwl float-end">
            <s v-show="arrival.originalTime" class="text-muted me-1">
                {{ arrival.originalTime?.toLocaleString(DateTime.TIME_SIMPLE) }}
            </s>
            <span data-bs-toggle="tooltip" :title="trans(timeTypeTooltip(arrival.type))">
                {{ arrival.time?.toLocaleString(DateTime.TIME_SIMPLE) }}
            </span>
        </span>
        <a
            :href="`/stationboard?stationId=${status.train.destination.id}&stationName=${status.train.destination.name}`"
            class="text-trwl clearfix"
        >
            {{ status.train.destination.name }}
        </a>
    </li>
</template>
