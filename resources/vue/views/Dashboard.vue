<script setup lang="ts">
import {trans} from "laravel-vue-i18n";
import StationAutocomplete from "../components/StationAutocomplete/StationAutocomplete.vue";
import {ref} from "vue";
import {Api, StatusResource, UserAuthResource} from "../../types/Api.gen";
import StatusCard from "../components/Status/StatusCard.vue";

const api = new Api({baseUrl: window.location.origin + '/api/v1'});
const statuses = ref<StatusResource[]>([]);
const user = ref<UserAuthResource | null>(null);

function fetchStatuses() {
  api.dashboard.getDashboard().then((response) => {
    response.json().then((data) => {
      statuses.value = data.data;
    });
  }).catch((error) => {
    console.error('Error fetching statuses:', error);
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

fetchStatuses();
fetchUser();
</script>

<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-7">
        <div id="station-board-new">
          <Apialerts></Apialerts>
          <StationAutocomplete :dashboard="true" :show-gps-button="true"></StationAutocomplete>
        </div>

        <!--
        todo: Future check-ins
        @if($future->count() >= 1)
        <div class="accordion accordion-flush" id="accordionFutureCheckIns">
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
                @include('includes.statuses', ['statuses' => $future, 'showDates' => false])
              </div>
            </div>
          </div>
        </div>
        @endif
        -->

        <!--
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
        <template v-for="status in statuses">
          <!-- todo: date header if status.date != previous.status.date -->
          <StatusCard :status="status" :authenticated-user="user"/>
        </template>

        @include('includes.statuses', ['statuses' => $statuses, 'showDates' => true])
        <!--
        Todo: Pagination
        {{ $statuses->links() }}

        -->

        <section
            v-if="statuses.length <= 0"
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
  </div>
</template>
