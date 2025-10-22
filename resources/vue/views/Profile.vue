<script setup lang="ts">
import {trans} from "laravel-vue-i18n";
import {computed, ref} from "vue";
import {Api, StatusResource, StopoverResource} from "../../types/Api.gen";
import StatusCard from "../components/Status/StatusCard.vue";
import {DateTime, Duration} from "luxon";
import {getDepartureForStatus} from "../helpers/DateTimeHelper";
import {Notyf} from "notyf";
import {useUserStore} from "../stores/user";
import LoadingSkeletonRows from "../components/Loader/LoadingSkeletonRows.vue";

const props = defineProps<{ username: string }>();

const api = new Api({baseUrl: window.location.origin + "/api/v1"});
const notyf = new Notyf({position: {x: "right", y: "bottom"}});

// -------------------------
// State
// -------------------------
const userData = ref<any>(null);
const statuses = ref<StatusResource[]>([]);
const stopovers = ref<Record<string, StopoverResource[]>>({});
const loadingUser = ref(true);
const loadingStatuses = ref(true);

const showMore = ref(false);
const currentPage = ref(1);
const lastPage = ref<number | null>(null);

const authUser = useUserStore();

async function fetchUser() {
  loadingUser.value = true;
  try {
    const res = await fetch(`/api/v1/user/${encodeURIComponent(props.username)}`, {
      headers: {Accept: "application/json"},
      credentials: "same-origin",
    });
    const json = await res.json();
    userData.value = json.data;
  } catch (err: any) {
    notyf.error("Error fetching user: " + err.message);
  } finally {
    loadingUser.value = false;
  }
}

async function fetchStatuses(append = false) {
  loadingStatuses.value = true;

  const nextPage = append ? currentPage.value + 1 : 1;
  const url = `/api/v1/user/${encodeURIComponent(props.username)}/statuses?page=${nextPage}`;

  try {
    const res = await fetch(url, {headers: {Accept: "application/json"}, credentials: "same-origin"});
    const json = await res.json();

    const list: StatusResource[] = json.data ?? [];
    if (append) statuses.value.push(...list);
    else statuses.value = list;

    const meta = json.meta ?? {};
    currentPage.value = meta.current_page ?? nextPage;
    lastPage.value = meta.last_page ?? null;

    if (lastPage.value === null) {
      showMore.value = !!(json.links?.next);
    } else {
      showMore.value = currentPage.value < lastPage.value;
    }

    fetchStopovers();
  } catch (err: any) {
    notyf.error("Error fetching statuses: " + err.message);
  } finally {
    loadingStatuses.value = false;
  }
}

async function fetchStopovers() {
  if (!statuses.value.length) return;
  const tripIds = [...new Set(statuses.value.map(s => s.train.trip.toString()))];
  if (!tripIds.length) return;

  try {
    const res = await api.stopovers.getStopOvers(tripIds.join(","));
    const json = await res.json();
    stopovers.value = json.data ?? {};
  } catch (err) {
    console.error("Stopovers error", err);
  }
}

function getStopoverForTrip(tripId: string) {
  return stopovers.value[tripId];
}

function isNewDay(index: number): boolean {
  if (index === 0) return true;
  const prevDt = getDepartureForStatus(statuses.value[index - 1]).dateTime;
  const currDt = getDepartureForStatus(statuses.value[index]).dateTime;
  return !currDt.hasSame(prevDt, 'day');
}

function dateTitleFor(s: StatusResource): string {
  return getDepartureForStatus(s).dateTime.toLocaleString(DateTime.DATE_HUGE);
}

function statsDailyHref(s: StatusResource): string {
  const dt = getDepartureForStatus(s).dateTime;
  return `/statistics/daily/${dt.toISODate()}`;
}

// Metrics
const kmDisplay = computed(() => {
  const meters = (userData.value?.trainDistance ?? 0);
  const km = meters / 1000;
  return km.toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1});
});
const durationParts = computed(() => {
  const minutes = userData.value?.trainDuration ?? 0;
  const dur = Duration.fromObject({minutes}).shiftTo("days", "hours", "minutes");
  return {d: dur.days ?? 0, h: dur.hours ?? 0, m: Math.round(dur.minutes ?? 0)};
});
const showPoints = computed(() =>
    !!(userData.value?.pointsEnabled || authUser.user?.pointsEnabled)
);

const mergedLinks = computed(() => {
  const links = [...(userData.value?.profileLinks ?? [])];
  const hasMastodon = links.some((l) => (l.name || "").toUpperCase() === "MASTODON");
  if (userData.value?.mastodonUrl && !hasMastodon) {
    links.push({name: "Mastodon", url: userData.value.mastodonUrl, icon: "fa-brands fa-mastodon"});
  }
  return links;
});

fetchUser();
fetchStatuses(false);
</script>

<template>
  <div class="row mt-4">
    <!-- LEFT COLUMN -->
    <div class="col">
      <!-- Stats card -->
      <div class="card mb-3 shadow-sm rounded">
        <div class="card-body">
          <LoadingSkeletonRows v-if="loadingUser" :columns="3" :rows="1"/>
          <div v-else class="row text-center gx-2 gy-3">
            <div class="col">
              <i class="fa fa-route fa-2x text-trwl"></i>
              <div class="h5 mb-0">
                {{ kmDisplay }}
                <small class="text-muted">km</small>
              </div>
            </div>
            <div class="col">
              <i class="fa fa-stopwatch fa-2x text-trwl"></i>
              <div class="h5 mb-0">
                {{ durationParts.d }}<small class="text-muted">d</small>&nbsp;
                {{ durationParts.h }}<small class="text-muted">h</small>&nbsp;
                {{ durationParts.m }}<small class="text-muted">min</small>
              </div>
            </div>
            <div class="col" v-if="showPoints">
              <i class="fa fa-dice-d20 fa-2x text-trwl"></i>
              <div class="h5 mb-0">
                {{ userData?.points ?? 0 }}
                <small class="text-muted">{{ trans('profile.points-abbr') }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bio & links -->
      <div
          v-if="userData?.bio || mergedLinks.length"
          class="card mb-3 shadow-sm rounded"
      >
        <div class="card-body">
          <p v-if="userData?.bio" class="text-muted fst-italic m-0">
            <i class="fa fa-quote-left me-2"></i>
            <span class="profile-bio" v-html="userData.bio"></span>
          </p>
          <div
              v-if="mergedLinks.length"
              class="d-flex justify-content-center flex-wrap gap-3 mt-4"
          >
            <a
                v-for="(l, i) in mergedLinks"
                :key="i"
                :href="l.url"
                class="text-muted fs-4"
                :aria-label="l.name"
                target="_blank"
                rel="me"
            >
              <i :class="l.icon || 'fa-solid fa-link'"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-8 col-lg-7">
      <h1 class="fs-3" v-if="statuses.length">
        {{ trans('profile.last-journeys-of') }} {{ userData?.displayName || props.username }}:
      </h1>

      <template v-for="(s, i) in statuses" :key="s.id">
        <h2 class="mb-2 fs-5" v-if="isNewDay(i)">
          {{ dateTitleFor(s) }}
          <a :href="statsDailyHref(s)" class="text-trwl" aria-label="Tägliche Fahrten">
            <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
          </a>
        </h2>

        <StatusCard
            :status="s"
            :authenticated-user="authUser.user"
            :stopovers="getStopoverForTrip(s.train.trip.toString())"
        />
      </template>

      <LoadingSkeletonRows v-if="loadingStatuses" class="text-center my-4" :rowHeight="120" :columns="1" :rows="3"/>

      <div v-if="!loadingStatuses && showMore" class="text-center my-4">
        <button class="btn btn-primary" @click="fetchStatuses(true)" :disabled="loadingStatuses">
          <i class="fa-solid fa-arrow-down"></i>
        </button>
        <div class="small text-muted mt-2" v-if="lastPage !== null">
          {{ currentPage }} / {{ lastPage }}
        </div>
      </div>

      <div
          v-if="!loadingStatuses && !showMore && statuses.length"
          class="text-center my-4"
      >
        <p class="text-muted">
          Final stop. All change, please!
        </p>
      </div>

      <div
          v-if="!loadingStatuses && !statuses.length"
          class="text-center my-4"
      >
          <span class="text-danger fs-3">
            <template v-if="(userData?.trainDistance ?? 0) > 0">
              {{ trans('profile.no-visible-statuses', {username: userData?.displayName}) }}
            </template>
            <template v-else>
              {{ trans('profile.no-statuses', {username: userData?.displayName}) }}
            </template>
          </span>
      </div>
    </div>
  </div>
</template>
