<script setup lang="ts">
import {PropType} from "vue";
import {StatusResource} from "../../../../types/Api.gen";
import {getArrivalAttribute, timeTypeTooltip} from "../../../helpers/DateTimeHelper";
import {trans} from "laravel-vue-i18n";
import {DateTime} from "luxon";

const props = defineProps({
  status: {
    type: Object as PropType<StatusResource>,
    required: true
  }
});

const arrival = getArrivalAttribute(props.status);
</script>

<template>
  <li>
    <i class="trwl-bulletpoint" aria-hidden="true"></i>
    <span class="text-trwl float-end">
      <s v-show="arrival.originalTime" class="text-muted">
        {{ arrival.originalTime?.toLocaleString(DateTime.TIME_SIMPLE) }}
      </s>
      <span data-bs-toggle="tooltip" :title="trans(timeTypeTooltip(arrival.type))">
        {{ arrival.time?.toLocaleString(DateTime.TIME_SIMPLE) }}
      </span>
    </span>
    <a href="#" class="text-trwl clearfix"><!-- todo: link to destination -->
      {{ status.train.destination.name }}
    </a>
  </li>
</template>
