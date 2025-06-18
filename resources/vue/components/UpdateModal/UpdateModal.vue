<script setup lang="ts">
import {PropType, ref, watch} from "vue";
import ModalComponent from "../ModalComponent.vue";
import {trans} from "laravel-vue-i18n";
import BusinessDropdown from "../BusinessDropdown.vue";
import {Api, StatusResource, StatusUpdateBody, StopoverResource} from "../../../types/Api.gen";
import VisibilityDropdown from "../VisibilityDropdown.vue";
import {Dtm} from "../../helpers/DateTime";
import {DateTime} from "luxon";

const emit = defineEmits(['status-updated']);
const props = defineProps({
  status: {
    type: Object as PropType<StatusResource>,
    required: true
  }
});

const modal = ref<ModalComponent | null>(null);
const api = new Api({baseUrl: window.location.origin + '/api/v1'});
const loading = ref(false);

// copy the status properties to a new object to avoid mutating the original prop
const updateStatus = ref<StatusUpdateBody | null>(null);
watch(() => props.status, (newStatus) => {
  updateStatus.value = {
    body: newStatus.body,
    business: newStatus.business,
    visibility: newStatus.visibility,
    manualDeparture: newStatus.train.manualDeparture,
    manualArrival: newStatus.train.manualArrival,
    destinationId: newStatus.train.destination.id,
  };
}, {immediate: true});

const show = () => {
  fetchDestinations();
  modal.value?.show();
}

function updateData() {
  loading.value = true;
  // Logic to update data goes here
  if (updateStatus.value) {
    updateStatus.value.destinationArrivalPlanned = stopovers.value.find(stopover => stopover.id === updateStatus.value!.destinationId)?.arrival || null;

    api.status.updateSingleStatus(updateStatus.value, props.status.id).then((status) => {
      emit('status-updated', status.data.data);
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

const stopovers = ref<StopoverResource[]>([]);

function fetchDestinations() {
  api.trains.getTrainTrip({
    hafasTripId: props.status.train.trip,
    lineName: props.status.train.lineName,
    start: props.status.train.origin.id
  }).then((response) => {
    stopovers.value = response.data?.data?.stopovers || [];
  }).catch((error) => {
    console.error('Error fetching destinations:', error);
  });
}


defineExpose({show});
</script>

<template>
  <ModalComponent ref="modal" :title="trans('modals.editStatus-title')">
    <template v-if="updateStatus !== null" #body>
      <div class="destination-wrapper form-floating mb-2">
        <select class="form-select" required id="form-status-destination" v-model="updateStatus.destinationId">
          <option v-for="stopover in stopovers" :key="stopover.id"
                  :value="stopover.id"
          >
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
            <!-- todo: fix pre filled values -->
            <input class="form-control" id="manual_departure"
                   type="datetime-local"
                   v-model="updateStatus.manualDeparture"
                   placeholder="{{trans('export.title.departure_real')}}"
            />
            <label for="manual_departure">
              {{ trans('export.title.departure_real') }}
            </label>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-floating mb-2">
            <!-- todo: fix pre filled values -->
            <input class="form-control" id="manual_arrival" v-model="updateStatus.manualArrival"
                   type="datetime-local"
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
                            class="btn btn-outline-primary"/>
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
