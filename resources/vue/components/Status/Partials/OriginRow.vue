<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { PropType, ref, watch } from 'vue';
import { Business, StatusResource } from '../../../../types/Api.gen';
import {
    getArrivalForStatus,
    getDepartureAttribute,
    getDepartureForStatus,
    timeTypeTooltip,
} from '../../../helpers/DateTimeHelper';
import { IconHelper } from '../../../helpers/IconHelper';
import LineIndicator from '../../LineIndicator.vue';
import ProductIcon from '../../ProductIcon.vue';
import DurationSpan from './DurationSpan.vue';

const props = defineProps({
    status: {
        type: Object as PropType<StatusResource>,
        required: true,
    },
});

const arrival = ref(getDepartureAttribute(props.status));
const duration = ref(
    getArrivalForStatus(props.status).dateTime.diff(getDepartureForStatus(props.status).dateTime, ['hours', 'minutes']),
);

watch(
    () => props.status,
    () => {
        arrival.value = getDepartureAttribute(props.status);
        duration.value = getArrivalForStatus(props.status).dateTime.diff(getDepartureForStatus(props.status).dateTime, [
            'hours',
            'minutes',
        ]);
    },
    {
        immediate: true,
    },
);
</script>

<template>
    <li>
        <i class="trwl-bulletpoint" aria-hidden="true" />
        <span class="text-trwl float-end" :class="{ 'cancelled-time': status.train.origin.cancelled }">
            <s v-show="arrival.originalTime" class="text-muted me-1">
                {{ arrival.originalTime?.toLocaleString(DateTime.TIME_SIMPLE) }}
            </s>
            <span data-bs-toggle="tooltip" :title="trans(timeTypeTooltip(arrival.type))">
                {{ arrival.time?.toLocaleString(DateTime.TIME_SIMPLE) }}
            </span>
        </span>

        <a
            :href="`/stationboard?stationId=${status.train.origin.id}&${status.train.origin.name}`"
            class="text-trwl clearfix"
            :class="{ 'cancelled-name': status.train.origin.cancelled }"
        >
            {{ status.train.origin.name }}
            <small v-if="status.train.origin.cancelled" class="badge cancelled-badge ms-1">{{
                $t('stationboard.stop-cancelled')
            }}</small>
        </a>

        <p class="train-status text-muted m-0">
            <span class="align-middle">
                <ProductIcon :mode="status.train.mode" :product="status.train.category" />

                <LineIndicator
                    class-name="line-badge align-middle line-indicator"
                    :product-name="status.train.category"
                    :number="status.train.lineName"
                    :mode="status.train.mode"
                    :color="status.train.routeTextColor"
                    :background-color="status.train.routeColor"
                />

                <small
                    v-if="status.train.manualJourneyNumber"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    :title="trans('status.manual_journey_number')"
                >
                    ({{ status.train.manualJourneyNumber }})
                </small>
                <small
                    v-else-if="
                        status.train.journeyNumber &&
                        !status.train.lineName.includes(status.train.journeyNumber.toString())
                    "
                >
                    ({{ status.train.journeyNumber }})
                </small>
            </span>

            <span class="ps-2">
                <i class="fa fa-route d-inline" aria-hidden="true" />&nbsp;
                <span v-if="status.train.distance < 1000">{{ status.train.distance }} <small>m</small></span>
                <span v-else>{{ (status.train.distance / 1000).toFixed(0) }} <small>km</small></span>
            </span>

            <span class="ps-2">
                <i class="fa fa-clock d-inline" aria-hidden="true" />&nbsp;
                <DurationSpan :duration="duration.as('seconds')" class="d-inline" />
            </span>

            <span v-if="status.business !== Business.Value0" class="ps-2">
                <i
                    class="fa"
                    :class="IconHelper.getBusinessIcon(status.business)"
                    aria-hidden="true"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    :title="trans(IconHelper.getBusinessTitle(status.business))"
                />
                <span class="sr-only">{{ trans(IconHelper.getBusinessTitle(status.business)) }}</span>
            </span>

            <template v-if="status.event">
                <br />
                <span class="pl-sm-2">
                    <i class="fa fa-calendar-day" aria-hidden="true" />
                    <span class="text-trwl">&nbsp;</span>
                    <a :href="`/event/${status.event.slug}`">
                        {{ status.event.name }}
                    </a>
                </span>
            </template>
        </p>
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
