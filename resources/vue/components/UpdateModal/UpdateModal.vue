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
import {getArrivalForStopover, getDepartureForStatus} from "../../helpers/DateTimeHelper";
import EventDropdown from "../EventDropdown.vue";
import DateTimeInput from "../Helpers/DateTimeInput.vue";

const emit = defineEmits(['status-updated']);
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

// copy the status properties to a new object to avoid mutating the original prop
const manualDeparture = ref<Date | null>(null);
const manualArrival = ref<Date | null>(null);
const updateStatus = ref<StatusUpdateBody | null>(null);
watch(() => props.status, (newStatus) => {
  updateStatus.value = {
    body: newStatus.body,
    business: newStatus.business,
    visibility: newStatus.visibility,
    manualDeparture: newStatus.train.manualDeparture,
    manualArrival: newStatus.train.manualArrival,
    destinationId: newStatus.train.destination.id,
    eventId: newStatus.event?.id || null,
  };
  manualDeparture.value = newStatus.train.manualDeparture ? new Date(newStatus.train.manualDeparture) : null;
  manualArrival.value = newStatus.train.manualArrival ? new Date(newStatus.train.manualArrival) : null;
}, {immediate: true});

const show = () => {
  fetchDestinations();
  eventsDropdown.value?.fetchEvents(getDepartureForStatus(props.status).toISO());
  modal.value?.show();
}

function updateData() {
  loading.value = true;
  // Logic to update data goes here
  if (updateStatus.value) {
    updateStatus.value.destinationArrivalPlanned = stopovers.value.find(stopover => stopover.id === updateStatus.value!.destinationId)?.arrivalPlanned || null;
    console.log(manualDeparture.value);
    console.log(manualArrival.value);
    updateStatus.value.manualDeparture = manualDeparture.value ? manualDeparture.value.toISOString() : null;
    updateStatus.value.manualArrival = manualArrival.value ? manualArrival.value.toISOString() : null;
    console.log(updateStatus.value);

    api.status.updateSingleStatus(updateStatus.value, props.status.id).then((status) => {
      emit('status-updated', status.data.data);
      if (status.data.data) {
        activeStatus.status = status.data.data;
      }
      loading.value = false;
      modal.value?.hide();
    }).catch((error) => {
      console.error('Error updating status:', error);
      // Optionally, you can show an error message to the user
    });
  } else {
    modal.value?.hide();
  }
}


function fetchDestinations() {
  api.trains.getTrainTrip({
    hafasTripId: props.status.train.trip,
    lineName: props.status.train.lineName,
    start: props.status.train.origin.id
  }).then((response) => {
    stopovers.value = response.data?.data?.stopovers || [];
    const departurePlanned = DateTime.fromISO(props.status.train.origin.departurePlanned || '');
    stopovers.value = stopovers.value.filter((stopover) => {
      return getArrivalForStopover(stopover).dateTime.diff(departurePlanned).as('minutes') >= 0;
    })
  }).catch((error) => {
    console.error('Error fetching destinations:', error);
  });
}

function selectEvent(event: EventResource | null) {
  if (updateStatus.value && event) {
    updateStatus.value.eventId = event.id;
  }
}

defineExpose({show});
</script>

<template>
  <ModalComponent ref="modal" :title="trans('modals.editStatus-title')">
    <template v-if="updateStatus !== null" #body>
      <div class="destination-wrapper form-floating mb-2">
        <select class="form-select" required id="form-status-destination" v-model="updateStatus.destinationId">
          <option v-for="stopover in stopovers" :key="stopover.id" :value="stopover.id">
            {{ new Dtm(stopover.arrival || '').toLocaleString(DateTime.TIME_SIMPLE) }}: {{ stopover.name }}
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
                placeholder="{{trans('export.title.departure_real')}}"
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
                placeholder="{{trans('export.title.arrival_real')}}"
            />
            <label for="manual_arrival">
              {{ trans('export.title.arrival_real') }}
            </label>
          </div>
        </div>
      </div>

      <div class="form-outline">
        <textarea class="form-control" name="body" id="status-body" maxlength="280"
                  :placeholder="trans('modals.editStatus-label')"
                  style="min-height: 130px;"
                  v-model="updateStatus.body"
        ></textarea>
      </div>
      <small class="text-muted float-end" v-show="updateStatus.body.length > 100">
        <span>
          {{ updateStatus.body.length }}
        </span>/280
      </small>

      <div class="py-2 px-3 border-bottom gap-2">
        <BusinessDropdown v-model="updateStatus.business" class="btn btn-outline-primary me-2"/>
        <VisibilityDropdown :start-value="updateStatus.visibility" v-model="updateStatus.visibility"
                            class="btn btn-outline-primary me-2"/>
        <EventDropdown :prefetch-events="false" ref="eventsDropdown" :pre-selected-event="status.event"
                       @select-event="selectEvent" class="btn btn-outline-primary"/>
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
