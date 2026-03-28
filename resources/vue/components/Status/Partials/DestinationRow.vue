<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { PropType, ref, watch } from 'vue';
import { StatusResource } from '../../../../types/Api.gen';
import { getArrivalAttribute, timeTypeTooltip } from '../../../helpers/DateTimeHelper';

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
        <span class="text-trwl float-end" :class="{ 'cancelled-time': status.train.destination.cancelled }">
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
            :class="{ 'cancelled-name': status.train.destination.cancelled }"
        >
            {{ status.train.destination.name }}
            <small v-if="status.train.destination.cancelled" class="badge cancelled-badge ms-1">{{
                $t('stationboard.stop-cancelled')
            }}</small>
        </a>
    </li>
</template>

<style scoped lang="scss">
@import '../../../../sass/_variables.scss';

.cancelled-time {
    text-decoration: line-through !important;
    text-decoration-color: $trwlRot !important;
    text-decoration-thickness: 2px !important;
}

.cancelled-name {
    text-decoration: line-through !important;
    text-decoration-color: $trwlRot !important;
    text-decoration-thickness: 2px !important;
}

.cancelled-badge {
    font-size: 0.6em;
    background-color: $trwlRot;
    color: white;
    border-radius: 3px;
    padding: 1px 5px;
    vertical-align: middle;
    text-decoration: none;
    display: inline-block;
}
</style>
