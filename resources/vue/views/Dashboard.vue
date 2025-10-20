<script setup lang="ts">
import {trans} from "laravel-vue-i18n";
import StationAutocomplete from "../components/StationAutocomplete/StationAutocomplete.vue";
import {ref} from "vue";
import {Api, StatusResource, StopoverResource} from "../../types/Api.gen";
import StatusCard from "../components/Status/StatusCard.vue";
import {DateTime} from "luxon";
import {getDepartureForStatus} from "../helpers/DateTimeHelper";
import {Notyf} from "notyf";
import {useUserStore} from "../stores/user";
import ApiAlerts from "../components/ApiAlerts.vue";
import LoadingSkeletonRows from "../components/Loader/LoadingSkeletonRows.vue";

const api = new Api({baseUrl: window.location.origin + '/api/v1'});
const statuses = ref<StatusResource[]>([]);
const loading = ref(true);
const currentPage = ref(1);
const showMore = ref(false);
const futureStatuses = ref<StatusResource[]>([]);
const stopovers = ref<Record<string, StopoverResource[]>>({});
const user = useUserStore();
const notyf = new Notyf({position: {x: "right", y: "bottom"}});

function fetchStatuses(page: number | undefined = undefined, append: boolean = false) {
  loading.value = true;
  showMore.value = false;
  api.dashboard.getDashboard({page}).then((response) => {
    response.json().then((data) => {
      if (append) {
        statuses.value.push(...data.data);
      } else {
        statuses.value = data.data;
      }
      fetchStopovers(append);
      loading.value = false;
      showMore.value = data.links?.next?.length > 0;
      currentPage.value = data.meta?.current_page || 0;
    });
  }).catch((error) => {
    loading.value = false;
    notyf.error('Error fetching statuses: ' + error.message);
  });
}

function fetchStopovers(append: boolean = false) {
  if (statuses.value.length === 0) {
    return;
  }

  const tripIds = statuses.value.map(status => status.train.trip.toString());
  api.stopovers.getStopOvers(tripIds.join(',')).then((response) => {
    response.json().then((data) => {
      if (append) {
        for (const tripId in data.data) {
          if (data.data.hasOwnProperty(tripId)) {
            if (!stopovers.value[tripId]) {
              stopovers.value[tripId] = [];
            }
            stopovers.value[tripId].push(...data.data[tripId]);
          }
        }
      } else {
        stopovers.value = data.data;
      }

    });
  }).catch((error) => {
    console.error('Error fetching stopovers:', error);
  });
}

function getStopoverForTrip(tripId: string): StopoverResource[] | undefined {
  return stopovers.value[tripId];
}

function fetchFutureStatuses() {
  api.dashboard.getFutureDashboard().then((response) => {
    response.json().then((data) => {
      futureStatuses.value = data.data;
    });
  }).catch((error) => {
    notyf.error(trans('generic.error') + ': ' + error.message);
  });
}

fetchStatuses();
fetchFutureStatuses();
</script>

<template>
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
      <div id="station-board-new">
        <ApiAlerts></ApiAlerts>
        <StationAutocomplete :dashboard="true" :show-gps-button="true"></StationAutocomplete>
      </div>

      <div v-if="futureStatuses.length" class="accordion accordion-flush" id="accordionFutureCheckIns">
        <div class="accordion-item">
          <h2 class="accordion-header" id="flush-headingOne">
            <button class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#future-check-ins"
                    aria-expanded="false"
                    aria-controls="future-check-ins"
            >
              {{ trans('dashboard.future') }}
            </button>
          </h2>
          <div id="future-check-ins"
               class="accordion-collapse collapse"
               aria-labelledby="flush-headingOne"
               data-bs-parent="#accordionFutureCheckIns"
          >
            <div class="accordion-body px-0">
              <StatusCard v-for="status in futureStatuses"
                          :key="status.id"
                          :status="status"
                          :authenticated-user="user.user"/>
            </div>
          </div>
        </div>
      </div>

      <!--
      todo: Year in Review
      @if(config('trwl.year_in_review.alert'))
      <div class="alert alert-info" role="region" aria-label="{{ trans('year-review') }}">
        <h4 class="alert-heading">
          <i class="fa-solid fa-champagne-glasses" aria-hidden="true"></i>
          Träwelling {{ trans('year-review') }}
        </h4>
        <p>{{ trans('year-review.teaser') }}</p>
        <a class="btn btn-outline-primary btn-block" href="/your-year/">
          <i class="fa-solid fa-arrow-pointer text-primary" aria-hidden="true"></i>
          {{ trans('year-review.open') }}
        </a>
      </div>
      @endif
      -->
      <template v-for="(status, index) in statuses">
        <h2 class="mb-2 fs-5"
            v-if="index === 0 || !DateTime.fromISO(status.train.origin.departure || '').hasSame(DateTime.fromISO(statuses[index - 1].train.origin.departure || ''), 'day')">
          {{ getDepartureForStatus(status).toLocaleString(DateTime.DATE_HUGE) }}
        </h2>
        <StatusCard
            :status="status"
            :authenticated-user="user.user"
            :stopovers="getStopoverForTrip(status.train.trip.toString())"
        />
      </template>

      <LoadingSkeletonRows v-if="loading" class="text-center my-4" :rowHeight="120"/>

      <div v-if="showMore" class="text-center my-4">
        <button class="btn btn-primary" @click="fetchStatuses(currentPage + 1, true)">
          <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
        </button>
      </div>
      <div v-if="!loading && !showMore && statuses.length > 0" class="text-center my-4">
        <p class="text-muted">
          Final stop. All change, please!
          <br/>
          <small>{{ trans('dashboard-end-seven-days') }}</small>
        </p>
      </div>

      <section
          v-if="!loading && statuses.length <= 0"
          class="alert alert-info"
          aria-label="{{ trans('dashboard.empty') }}"
      >
        <h4 class="alert-heading">
          <i class="fa-solid fa-binoculars" aria-hidden="true"></i>
          {{ trans('dashboard.empty') }}
        </h4>
        <p>{{ trans('dashboard.empty.teaser') }}</p>
        <p>
          {{ trans('dashboard.empty.discover1') }}
          <a href="{{ route('statuses.active') }}">
            {{ trans('menu.active') }}
          </a>
          {{ trans('dashboard.empty.discover3') }}.
        </p>
      </section>
    </div>
  </div>
</template>
