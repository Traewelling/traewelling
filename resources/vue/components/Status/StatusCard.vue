<script setup lang="ts">
import { Tooltip } from 'bootstrap';
import { onBeforeUnmount, onMounted, PropType, ref, useTemplateRef } from 'vue';
import { StatusResource, StopoverResource } from '../../../types/Api.gen';
import {
    getArrivalForStatus,
    getArrivalForStopover,
    getDepartureForStatus,
    getDepartureForStopover,
} from '../../helpers/DateTimeHelper';
import { useActiveCheckin } from '../../stores/activeCheckin';
import { useUserStore } from '../../stores/user';
import ActiveJourneyMap from '../ActiveJourneyMap.vue';
import Map from '../Map/Map.vue';
import DestinationRow from './Partials/DestinationRow.vue';
import NextStop from './Partials/NextStop.vue';
import OriginRow from './Partials/OriginRow.vue';
import StatusBody from './Partials/StatusBody.vue';
import StatusFooter from './Partials/StatusFooter.vue';

const props = defineProps({
    status: {
        type: Object as PropType<StatusResource>,
        required: true,
    },
    showMap: {
        type: Boolean,
        default: false,
    },
    stopovers: {
        type: Array as PropType<StopoverResource[]>,
        default: () => [],
    },
});
const emit = defineEmits(['status-liked', 'status-unliked', 'status-deleted', 'status-updated']);
const userStore = useUserStore();
userStore.fetchSettings();

const statusObject = ref<StatusResource>(props.status);
const progress = ref(0);
const interval = ref<number | null>(null);
const deleted = ref(false);
const deleting = ref(false);
const map = useTemplateRef('map');
const departure = getDepartureForStopover(statusObject.value.train.origin).dateTime;
const arrival = getArrivalForStopover(statusObject.value.train.destination).dateTime;
const activeCheckin = useActiveCheckin();
const rootEl = ref<HTMLElement | null>(null);
let delegatedTip: Tooltip | null = null;

onMounted(() => {
    if (!rootEl.value) return;
    delegatedTip = new Tooltip(rootEl.value, {
        selector: '[data-bs-toggle="tooltip"]',
        trigger: 'hover focus',
        container: 'body',
    });
    updateProgress();
});

onBeforeUnmount(() => {
    delegatedTip?.dispose();
    delegatedTip = null;
    if (interval.value !== null) {
        clearInterval(interval.value);
        interval.value = null;
    }
});

function clamp(val: number, min = 0, max = 100) {
    return Math.min(max, Math.max(min, val));
}

function calculateProgress(): number {
    const startMillis = Number(getDepartureForStatus(statusObject.value).toMillis());
    const endMillis = Number(getArrivalForStatus(statusObject.value).toMillis());
    const nowMs = Date.now();

    if (!Number.isFinite(startMillis) || !Number.isFinite(endMillis)) {
        return 0;
    }

    const duration = endMillis - startMillis;

    if (duration <= 0) {
        if (nowMs < startMillis) return 0;
        return 100;
    }

    if (nowMs <= startMillis) return 0;
    if (nowMs >= endMillis) return 100;

    const pct = ((nowMs - startMillis) / duration) * 100;
    return clamp(pct);
}

function ensureInterval() {
    if (progress.value > 0 && progress.value < 100) {
        if (interval.value === null) {
            interval.value = window.setInterval(() => {
                updateProgress();
            }, 1000);
        }
    } else {
        if (interval.value !== null) {
            clearInterval(interval.value);
            interval.value = null;
        }
    }
}

function updateProgress() {
    progress.value = calculateProgress();
    ensureInterval();
}

function deleteSelf() {
    if (activeCheckin.status?.id === statusObject.value.id) {
        activeCheckin.$reset();
    }
    deleted.value = true;
    if (props.showMap) {
        window.location.href = '/dashboard';
    }
}

function statusUpdated(status: StatusResource) {
    statusObject.value = status;
    updateProgress();
    if (props.showMap && map.value) {
        map.value.fetchStatusPolyline();
    }
}
</script>

<template>
    <Transition>
        <div v-show="!deleted" ref="rootEl" class="card status mb-3 position-relative">
            <div v-if="showMap" class="card-img-top">
                <div
                    id="activeJourneys"
                    class="statusMap embed-responsive"
                    :class="{ 'map embed-responsive-16by9': !userStore?.hasBeta }"
                >
                    <Map v-if="userStore?.hasBeta" :statuses="[statusObject]" />
                    <ActiveJourneyMap
                        v-else
                        ref="map"
                        :status-id="statusObject.id"
                        :departure="departure.toSeconds()"
                        :arrival="arrival.toSeconds()"
                        :line-color="statusObject.train.routeColor"
                    />
                </div>
            </div>
            <div class="card-body row">
                <!-- Big profile picture -->
                <div class="col-2 image-box pe-0 d-none d-lg-flex">
                    <a :href="`/@${statusObject.userDetails.username}`">
                        <img
                            loading="lazy"
                            decoding="async"
                            :src="statusObject.userDetails.profilePicture"
                            :alt="statusObject.userDetails.username"
                        />
                    </a>
                </div>

                <div class="col ps-0">
                    <ul class="timeline">
                        <OriginRow :status="statusObject" />
                        <StatusBody v-if="statusObject.body" :status="statusObject" class="mt-1" />
                        <NextStop :stopovers="stopovers" :in-progress="progress > 0 && progress < 100" />
                        <DestinationRow :status="statusObject" />
                    </ul>
                </div>
            </div>
            <!-- /card-body -->
            <!-- progress bar -->
            <div class="progress">
                <div
                    class="progress-bar"
                    role="progressbar"
                    :class="{ 'progress-pride': statusObject.event?.isPride }"
                    :style="`width: ${progress}%;`"
                />
            </div>

            <!-- delete loading overlay -->
            <Transition name="delete-overlay">
                <div v-if="deleting" class="delete-overlay">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="visually-hidden">Loading</span>
                    </div>
                </div>
            </Transition>

            <!-- footer -->
            <StatusFooter
                :status="statusObject"
                @status-deleted="deleteSelf()"
                @status-deleting="deleting = $event"
                @status-liked="emit('status-liked')"
                @status-unliked="emit('status-unliked')"
                @status-updated="statusUpdated"
            />
        </div>
    </Transition>
</template>

<style lang="scss" scoped>
/* we will explain what these classes do next! */
.v-leave-active {
    transition: opacity 0.5s ease;
}

.v-leave-to {
    opacity: 0;
}

.delete-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: inherit;

    :root.dark & {
        background: rgba(0, 0, 0, 0.45);
    }
}

.delete-overlay-enter-active,
.delete-overlay-leave-active {
    transition: opacity 0.15s ease;
}

.delete-overlay-enter-from,
.delete-overlay-leave-to {
    opacity: 0;
}
</style>
