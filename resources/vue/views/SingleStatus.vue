<script setup lang="ts">
import {ref} from "vue";
import {Api, StatusResource, UserAuthResource} from "../../types/Api.gen";
import StatusCard from "../components/Status/StatusCard.vue";
import {trans} from "laravel-vue-i18n";
import {getDepartureForStatus} from "../helpers/DateTimeHelper";
import {DateTime} from "luxon";

const loading = ref(true);
const status = ref<StatusResource | null>(null);
const user = ref<UserAuthResource | null>(null);
const pageError = ref<string | null>(null);

const api = new Api({baseUrl: window.location.origin + '/api/v1'});

function fetchStatus() {
  const statusId = window.location.pathname.split('/').pop();
  if (!statusId) {
    console.error('Status ID not found in URL');
    return;
  }

  api.status.getSingleStatus(parseInt(statusId)).then((response) => {
    response.json().then((data) => {
      loading.value = false;
      status.value = data.data;
    });
  }).catch((error) => {
    loading.value = false;
    if (error.status === 404) {
      pageError.value = trans('error.404');
    } else if (error.status === 403) {
      pageError.value = trans('error.403');
    }
    console.error('Error fetching status:', error);
  });
}

function fetchUser() {
  api.auth.getAuthenticatedUser().then((response) => {
    response.json().then((data) => {
      user.value = data.data;
    });
  }).catch((error) => {
    console.error('Error fetching user:', error);
  });
}

fetchStatus();
fetchUser();
</script>

<template>
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
      <div v-if="loading">
        <p class="text-center mt-5">
          <i class="fas fa-spinner fa-spin"></i> Loading status...
        </p>
      </div>
      <template v-else-if="status">
        <h2 class="fs-5">{{ getDepartureForStatus(status).toLocaleString(DateTime.DATE_HUGE) }}</h2>
        <StatusCard :status :show-map="true" :authenticated-user="user"/>
      </template>
      <h2 v-if="pageError">{{ pageError }} :(</h2>
    </div>
  </div>
</template>

<style scoped lang="scss">

</style>
