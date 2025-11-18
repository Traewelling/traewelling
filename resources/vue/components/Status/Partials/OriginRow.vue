<script setup lang="ts">
import {PropType, ref, watch} from "vue";
import {Business, StatusResource} from "../../../../types/Api.gen";
import {
  getArrivalForStatus,
  getDepartureAttribute,
  getDepartureForStatus,
  timeTypeTooltip
} from "../../../helpers/DateTimeHelper";
import {trans} from "laravel-vue-i18n";
import {DateTime} from "luxon";
import ProductIcon from "../../ProductIcon.vue";
import {IconHelper} from "../../../helpers/IconHelper";
import DurationSpan from "./DurationSpan.vue";
import LineIndicator from "../../LineIndicator.vue";

const props = defineProps({
  status: {
    type: Object as PropType<StatusResource>,
    required: true
  }
});

const arrival = ref(getDepartureAttribute(props.status));
const duration = ref(getArrivalForStatus(props.status).dateTime.diff(getDepartureForStatus(props.status).dateTime, ['hours', 'minutes']));

watch(() => props.status, () => {
  arrival.value = getDepartureAttribute(props.status);
  duration.value = getArrivalForStatus(props.status).dateTime.diff(getDepartureForStatus(props.status).dateTime, ['hours', 'minutes']);
}, {
  immediate: true
});

function routeColorCss(hex?: string | null): string | null {
  if (!hex) return null;
  const clean = String(hex).replace(/[^0-9a-fA-F]/g, "");
  if (clean.length !== 6) return null;
  return `#${clean}`;
}

function contrastTextColor(hex?: string | null): string {
  const c = routeColorCss(hex);
  if (!c) return "inherit";
  const r = parseInt(c.slice(1, 3), 16);
  const g = parseInt(c.slice(3, 5), 16);
  const b = parseInt(c.slice(5, 7), 16);
  const yiq = (r * 299 + g * 587 + b * 114) / 1000;
  return yiq >= 180 ? "#000" : "#fff";
}
</script>

<template>
  <li>
    <i class="trwl-bulletpoint" aria-hidden="true"></i>
    <span class="text-trwl float-end">
      <s v-show="arrival.originalTime" class="text-muted me-1">
        {{ arrival.originalTime?.toLocaleString(DateTime.TIME_SIMPLE) }}
      </s>
      <span data-bs-toggle="tooltip" :title="trans(timeTypeTooltip(arrival.type))">
        {{ arrival.time?.toLocaleString(DateTime.TIME_SIMPLE) }}
      </span>
    </span>

    <a :href="`/stationboard?stationId=${status.train.origin.id}&${status.train.origin.name}`"
       class="text-trwl clearfix">
      {{ status.train.origin.name }}
    </a>

    <p class="train-status text-muted m-0">
      <span class="align-middle">
        <ProductIcon :mode="status.train.mode" :product="status.train.category"/>

        <LineIndicator
            class-name="line-badge align-middle line-indicator"
            :product-name="status.train.category"
                       :number="status.train.lineName"
                       :mode="status.train.mode"
                       :color="contrastTextColor(status.train.routeColor)"
                       :background-color="status.train.routeColor"/>

        <small v-if="status.train.manualJourneyNumber" data-bs-toggle="tooltip"
               data-bs-placement="top" :title="trans('status.manual_journey_number')">
          ({{ status.train.manualJourneyNumber }})
        </small>
        <small
            v-else-if="status.train.journeyNumber && !status.train.lineName.includes(status.train.journeyNumber.toString())">
          ({{ status.train.journeyNumber }})
        </small>
      </span>

      <span class="ps-2">
        <i class="fa fa-route d-inline" aria-hidden="true"></i>&nbsp;
        <span v-if="status.train.distance < 1000">{{ status.train.distance }} <small>m</small></span>
        <span v-else>{{ (status.train.distance / 1000).toFixed(0) }} <small>km</small></span>
      </span>

      <span class="ps-2">
        <i class="fa fa-clock d-inline" aria-hidden="true"></i>&nbsp;
        <DurationSpan :duration="duration.as('seconds')" class="d-inline"/>
      </span>

      <span v-if="status.business !== Business.Value0" class="ps-2">
        <i class="fa" :class="IconHelper.getBusinessIcon(status.business)" aria-hidden="true" data-bs-toggle="tooltip"
           data-bs-placement="top" :title="trans(IconHelper.getBusinessTitle(status.business))"></i>
        <span class="sr-only">{{ trans(IconHelper.getBusinessTitle(status.business)) }}</span>
      </span>

      <template v-if="status.event">
        <br>
        <span class="pl-sm-2">
          <i class="fa fa-calendar-day" aria-hidden="true"></i>
          <span class="text-trwl">&nbsp;</span>
          <a :href="`/event/${status.event.slug}`">
            {{ status.event.name }}
          </a>
        </span>
      </template>

    </p>
  </li>
</template>

<style scoped>
.line-badge {
  display: inline-block;
  padding: 0.05rem 0.35rem;
  margin: 0 0.25rem 0 0.35rem;
  border-radius: 0.35rem;
  line-height: 1.1;
  font-weight: 600;
  font-size: 0.95em;
  vertical-align: baseline;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.06) inset;
}
</style>
