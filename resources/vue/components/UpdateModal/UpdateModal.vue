<script setup lang="ts">
import {PropType, ref, useTemplateRef, watch} from "vue";
import ModalComponent from "../ModalComponent.vue";
import {trans} from "laravel-vue-i18n";
import BusinessDropdown from "../BusinessDropdown.vue";
import {Api, EventResource, StatusResource, StatusUpdateBody, StopoverResource} from "../../../types/Api.gen";
import VisibilityDropdown from "../VisibilityDropdown.vue";
import {Dtm} from "../../helpers/DateTime";
import {DateTime} from "luxon";
import {useActiveCheckin} from "../../stores/activeCheckin";
import {getDepartureForStatus} from "../../helpers/DateTimeHelper";
import EventDropdown from "../EventDropdown.vue";
import DateTimeInput from "../Helpers/DateTimeInput.vue";

const emit = defineEmits<{
  (e: "status-updated", status: StatusResource): void
}>();

const props = defineProps({
  status: {
    type: Object as PropType<StatusResource>,
    required: true
  }
});

const modal = ref<ModalComponent | null>(null);
const eventsDropdown = useTemplateRef('eventsDropdown');
const stopovers = ref<StopoverResource[]>([]);
const api = new Api({baseUrl: window.location.origin + '/api/v1'});
const activeStatus = useActiveCheckin();
const loading = ref(false);

/**
 * Important for ring lines!
 * Format: "<destinationId>|<destinationArrivalPlannedISO>"
 */
const destinationSelection = ref<string | null>(null);

const manualDeparture = ref<Date | null>(null);
const manualArrival = ref<Date | null>(null);
const updateStatus = ref<StatusUpdateBody | null>(null);

function makeDestinationValue(stationId: number | string, arrivalPlannedIso: string | null | undefined): string | null {
  if (!stationId || !arrivalPlannedIso) return null;
  return `${stationId}|${arrivalPlannedIso}`;
}

function parseDestinationValue(value: string | null): { id: number | null, arrivalPlanned: string | null } {
  if (!value) return {id: null, arrivalPlanned: null};
  const idx = value.indexOf("|");
  if (idx === -1) return {id: null, arrivalPlanned: null};
  const idPart = value.slice(0, idx);
  const tsPart = value.slice(idx + 1);
  const idNum = Number(idPart);
  if (!Number.isFinite(idNum) || !tsPart) return {id: null, arrivalPlanned: null};
  return {id: idNum, arrivalPlanned: tsPart};
}

watch(
    () => props.status,
    (newStatus) => {
      updateStatus.value = {
        body: newStatus.body,
        business: newStatus.business,
        visibility: newStatus.visibility,
        manualDeparture: newStatus.train.manualDeparture,
        manualArrival: newStatus.train.manualArrival,
        destinationId: newStatus.train.destination.id,
        // destinationArrivalPlanned: will be set while summiting
        eventId: newStatus.event?.id || null,
      };

      manualDeparture.value = newStatus.train.manualDeparture ? new Date(newStatus.train.manualDeparture) : null;
      manualArrival.value = newStatus.train.manualArrival ? new Date(newStatus.train.manualArrival) : null;

      destinationSelection.value = null;
    },
    {immediate: true}
);

function show() {
  fetchDestinations().then(() => {
    eventsDropdown.value?.fetchEvents(getDepartureForStatus(props.status).toISO());
    const currentDestStationId = props.status.train.destination.id;
    const currentDestArrivalPlanned = props.status.train.destination.arrivalPlanned;
    const found = stopovers.value.find(so => {
      return (so.id === currentDestStationId && so.arrivalPlanned === currentDestArrivalPlanned);
    });

    if (found?.arrivalPlanned) {
      destinationSelection.value = makeDestinationValue(found.id as unknown as number, found.arrivalPlanned);
      if (updateStatus.value) {
        updateStatus.value.destinationId = Number(found.id);
        updateStatus.value.destinationArrivalPlanned = found.arrivalPlanned;
      }
    }
    modal.value?.show();
  });
}

async function fetchDestinations() {
  try {
    const response = await api.trains.getTrainTrip({
      hafasTripId: props.status.train.trip,
      lineName: props.status.train.lineName,
      start: props.status.train.origin.id
    });

    const all = response.data?.data?.stopovers || [];
    const departurePlanned = DateTime.fromISO(props.status.train.origin.departurePlanned || '');
    stopovers.value = all.filter((stopover: StopoverResource) => {
      const arrival = DateTime.fromISO(stopover.arrivalPlanned || stopover.arrival || stopover.departurePlanned || stopover.departure);
      return arrival.diff(departurePlanned).as('minutes') >= 0;
    });
  } catch (e) {
    console.error('Error fetching destinations:', e);
  }
}

function onDestinationChange() {
  if (!updateStatus.value) return;
  const parsed = parseDestinationValue(destinationSelection.value);
  updateStatus.value.destinationId = parsed.id ?? undefined;
  updateStatus.value.destinationArrivalPlanned = parsed.arrivalPlanned ?? undefined;
}

function updateData() {
  loading.value = true;

  if (!updateStatus.value) {
    modal.value?.hide();
    return;
  }

  onDestinationChange();

  updateStatus.value.manualDeparture = manualDeparture.value ? manualDeparture.value.toISOString() : null;
  updateStatus.value.manualArrival = manualArrival.value ? manualArrival.value.toISOString() : null;

  api.status.updateSingleStatus(updateStatus.value, props.status.id)
      .then((status) => {
        emit('status-updated', status.data.data);
        if (status.data.data) {
          activeStatus.status = status.data.data;
        }
        loading.value = false;
        modal.value?.hide();
      })
      .catch((error) => {
        console.error('Error updating status:', error);
        loading.value = false;
      });
}

function selectEvent(event: EventResource | null) {
  if (updateStatus.value) {
    updateStatus.value.eventId = event ? event.id : null;
  }
}

defineExpose({show});
</script>

<template>
  <ModalComponent ref="modal" :title="trans('modals.editStatus-title')">
    <template v-if="updateStatus !== null" #body>
      <div class="destination-wrapper form-floating mb-2">
        <select
            class="form-select"
            required
            id="form-status-destination"
            v-model="destinationSelection"
            @change="onDestinationChange"
        >
          <option
              v-for="stopover in stopovers"
              :key="`${stopover.id}-${stopover.arrivalPlanned}`"
              :value="`${stopover.id}|${stopover.arrivalPlanned}`"
          >
            {{ new Dtm(stopover.arrivalPlanned || stopover.arrival || '').toLocaleString(DateTime.TIME_SIMPLE) }}:
            {{ stopover.name }}
          </option>
        </select>
        <label class="form-label" for="form-status-destination">
          {{ trans('exit') }}
        </label>
      </div>

      <div class="row">
        <div class="col-sm-6">
          <div class="form-floating mb-2">
            <DateTimeInput
                class="form-control"
                id="manual_departure"
                v-model="manualDeparture"
                :placeholder="trans('export.title.departure_real')"
            />
            <label for="manual_departure">
              {{ trans('export.title.departure_real') }}
            </label>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-floating mb-2">
            <DateTimeInput
                class="form-control"
                id="manual_arrival"
                v-model="manualArrival"
                :placeholder="trans('export.title.arrival_real')"
            />
            <label for="manual_arrival">
              {{ trans('export.title.arrival_real') }}
            </label>
          </div>
        </div>
      </div>

      <div class="form-outline">
        <textarea
            class="form-control"
            name="body"
            id="status-body"
            maxlength="280"
            :placeholder="trans('modals.editStatus-label')"
            style="min-height: 130px;"
            v-model="updateStatus.body"
        ></textarea>
      </div>
      <small class="text-muted float-end" v-show="(updateStatus.body || '').length > 100">
        <span>{{ (updateStatus.body || '').length }}</span>/280
      </small>

      <div class="py-2 gap-2">
        <BusinessDropdown v-model="updateStatus.business" class="btn btn-outline-primary me-2"/>
        <VisibilityDropdown :start-value="updateStatus.visibility" v-model="updateStatus.visibility"
                            class="btn btn-outline-primary me-2"/>
        <EventDropdown
            :prefetch-events="false"
            ref="eventsDropdown"
            :pre-selected-event="status.event"
            @select-event="selectEvent"
            class="btn btn-outline-primary"
        />
      </div>
    </template>

    <template #footer>
      <button type="button" class="btn btn-primary" @click="updateData()" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        {{ trans('modals.edit-confirm') }}
      </button>
    </template>
  </ModalComponent>
</template>

<style scoped>
.form-floating > .form-control:not(:placeholder-shown) ~ label::after {
  background-color: transparent;
}

.form-control:focus {
  box-shadow: none;
}
</style>
