<script setup lang="ts">
import {PropType, ref} from "vue";
import {StatusResource, StopoverResource} from "../../../types/Api.gen";
import {
  getArrivalForStatus,
  getArrivalForStopover,
  getDepartureForStatus,
  getDepartureForStopover
} from "../../helpers/DateTimeHelper";
import ActiveJourneyMap from "../ActiveJourneyMap.vue";
import {now} from "lodash";
import StatusFooter from "./Partials/StatusFooter.vue";
import DestinationRow from "./Partials/DestinationRow.vue";
import OriginRow from "./Partials/OriginRow.vue";
import NextStop from "./Partials/NextStop.vue";
import {useActiveCheckin} from "../../stores/activeCheckin";

const props = defineProps({
  status: {
    type: Object as PropType<StatusResource>,
    required: true
  },
  showMap: {
    type: Boolean,
    default: false
  },
  stopovers: {
    type: Array as PropType<StopoverResource[]>,
    default: () => []
  }
});

const emit = defineEmits(['status-liked', 'status-unliked', 'status-deleted']);
const progress = ref(0);
const interval = ref<number | null>(null);
const deleted = ref(false);
const departure = getDepartureForStopover(props.status.train.origin).dateTime;
const arrival = getArrivalForStopover(props.status.train.destination).dateTime;
const activeCheckin = useActiveCheckin();

function updateProgress() {
  progress.value = calculateProgress();

  if (progress.value >= 100 && interval.value !== null) {
    clearInterval(interval.value);
    interval.value = null;
  } else if (progress.value < 0 && interval.value === null) {
    setTimeout(() => {
      updateProgress();
      interval.value = setInterval(updateProgress, 1000); // Update every second
    }, now() % 1000); // Wait until the next second to start updating
  }
}

function calculateProgress() {
  const start = getDepartureForStatus(props.status).toMillis();
  const end = getArrivalForStatus(props.status).toMillis();
  const now = Date.now();
  if (start === end) {
    return now < start ? 0 : 1; // If start and end are the same, return 0 if now is before start, otherwise return 1
  }

  return 100 * (now - start) / (end - start);
}

function deleteSelf() {
  if (activeCheckin.status?.id === props.status.id) {
    activeCheckin.$reset()
  }
  deleted.value = true;
  if (props.showMap) {
    window.location.href = '/dashboard';
  }
}

updateProgress();
</script>

<template>
  <div class="card status mb-3" v-show="!deleted">
    <div v-if="showMap" class="card-img-top">
      <div id="activeJourneys" class="map statusMap embed-responsive embed-responsive-16by9">
        <ActiveJourneyMap
            :status-id="status.id"
            :departure="departure.toSeconds()"
            :arrival="arrival.toSeconds()"
        >
        </ActiveJourneyMap>
      </div>
    </div>
    <div class="card-body row">
      <!-- Big profile picture -->
      <div class="col-2 image-box pe-0 d-none d-lg-flex">
        <a :href="`/@${status.userDetails.username}`">
          <img loading="lazy" decoding="async" :src="status.userDetails.profilePicture"
               :alt="status.userDetails.username">
        </a>
      </div>

      <div class="col ps-0">
        <ul class="timeline">
          <OriginRow :status="status"/>
          <NextStop :stopovers="stopovers" :in-progress="progress > 0 && progress < 100"/>
          <DestinationRow :status="status"/>
        </ul>
      </div>
    </div>
    <!-- /card-body -->
    <!-- progress bar -->
    <div class="progress">
      <div
          class="progress-bar"
          role="progressbar"
          :class="{ 'progress-pride': status.event?.isPride }"
          :style="`width: ${progress}%;`"
      ></div>
    </div>

    <!-- footer -->
    <StatusFooter :status="status" @statusDeleted="deleteSelf()" @statusLiked="emit('status-liked')"
                  @statusUnliked="emit('status-unliked')"/>
  </div>
</template>
