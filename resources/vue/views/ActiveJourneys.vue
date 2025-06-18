<script setup lang="ts">
import {trans, transChoice} from "laravel-vue-i18n";
import {ref} from "vue";
import {Api, StatusResource} from "../../types/Api.gen";
import StatusCard from "../components/Status/StatusCard.vue";
import ActiveJourneyMap from "../components/ActiveJourneyMap.vue";
import {Notyf} from "notyf";
import {useUserStore} from "../stores/user";

const api = new Api({baseUrl: window.location.origin + '/api/v1'});
const statuses = ref<StatusResource[]>([]);
const loading = ref(true);
const user = useUserStore();
const notyf = new Notyf({position: {x: "right", y: "bottom"}});

function fetchStatuses() {
  loading.value = true;
  api.statuses.getActiveStatuses().then((response) => {
    response.json().then((data) => {
      statuses.value = data.data;
      loading.value = false;
    });
  }).catch((error) => {
    loading.value = false;
    notyf.error('Error fetching statuses: ' + error.message);
  });
}

fetchStatuses();

setInterval(() => {
  fetchStatuses();
}, 10000); // Refresh the map every 10 seconds
</script>

<template>
  <div class="row">
    <div class="col-12">
      <h1 class="fs-4">{{ trans('menu.active') }}</h1>
    </div>
    <div class="col-md-6 mb-4" id="activeJourneys">
      <ActiveJourneyMap :map-provider="user.user?.mapProvider || 'default'" ref="map"/>

      <div class="row text-center fs-5 mt-3">
        <div class="col mb-3">
          <i class="fa-solid fa-train"></i>
          {{ statuses.length }}
          {{ transChoice('active-journeys', statuses.length) }}
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div v-if="statuses.length === 0" class="alert alert-danger text-center">
        <strong class="fs-4">{{ trans('empty-en-route') }}</strong>
      </div>

      <StatusCard v-for="status in statuses"
                  :key="status.id"
                  :status="status"
                  :authenticated-user="user.user"
                  :show-map="false"
      />
    </div>
  </div>
</template>
