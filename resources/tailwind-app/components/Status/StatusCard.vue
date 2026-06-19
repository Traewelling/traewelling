<script setup lang="ts">
import {
    AlertTriangle,
    ArrowRight,
    Briefcase,
    Building2,
    Calendar,
    Clock,
    Eye,
    EyeOff,
    Heart,
    Lock,
    Route,
} from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Api, Business, MentionDto, StatusResource, StopoverResource, UserResource } from '../../../types/Api.gen';
import LineIndicator from '../../../vue/components/LineIndicator.vue';
import TrwMap from '../../../vue/components/Map/Map.vue';
import ProductIcon from '../../../vue/components/ProductIcon.vue';
import { Dtm } from '../../../vue/helpers/DateTime';
import {
    getArrivalAttribute,
    getArrivalForStatus,
    getArrivalForStopover,
    getDepartureAttribute,
    getDepartureForStatus,
    getDepartureForStopover,
    StopoverTimeType,
    timeTypeTooltip,
} from '../../../vue/helpers/DateTimeHelper';
import { useUserStore } from '../../../vue/stores/user';
import { VISIBILITY_ICONS } from '../../helpers/visibility';
import DistanceSpan from '../Stats/DistanceSpan.vue';
import DurationSpan from '../Stats/DurationSpan.vue';
import StatusContextMenu from './StatusContextMenu.vue';
import StatusDeleteModal from './StatusDeleteModal.vue';
import StatusEditModal from './StatusEditModal.vue';

const props = defineProps<{
    status: StatusResource;
    stopovers?: StopoverResource[];
    showMap?: boolean;
}>();

const emit = defineEmits<{
    'status-liked': [];
    'status-unliked': [];
    'status-deleted': [id: number];
    'status-updated': [status: StatusResource];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const userStore = useUserStore();

const statusObject = ref<StatusResource>(props.status);
const deleted = ref(false);
const showDeleteModal = ref(false);
const showEditModal = ref(false);
const progress = ref(0);
const interval = ref<number | null>(null);
const likes = ref(props.status.likes ?? 0);

// next stop logic
const nextStop = ref<StopoverResource | null>(null);
const isAtStop = ref(false);

watch(
    () => props.status,
    (s) => {
        statusObject.value = s;
        likes.value = s.likes ?? 0;
        updateProgress();
    },
);

const departure = computed(() => getDepartureAttribute(statusObject.value));
const arrival = computed(() => getArrivalAttribute(statusObject.value));

function fmtTime(dtm: Dtm | null): string {
    if (!dtm) return '?';
    return dtm.dateTime.toFormat('HH:mm');
}

function delayClass(stopoverTime: { time: Dtm | null; originalTime: Dtm | null; type: StopoverTimeType }): string {
    if (stopoverTime.type === StopoverTimeType.Planned) return '';
    const minutes = stopoverTime.originalTime
        ? ((stopoverTime.time?.dateTime.toMillis() ?? 0) - stopoverTime.originalTime.dateTime.toMillis()) / 60000
        : 0;
    if (minutes <= 0) return 'text-success';
    if (minutes < 6) return 'text-warning font-medium';
    return 'text-error font-medium';
}

function calculateProgress(): number {
    const start = getDepartureForStatus(statusObject.value).dateTime.toMillis();
    const end = getArrivalForStatus(statusObject.value).dateTime.toMillis();
    const now = Date.now();
    if (!Number.isFinite(start) || !Number.isFinite(end) || end <= start) return now >= end ? 100 : 0;
    if (now <= start) return 0;
    if (now >= end) return 100;
    return Math.min(100, Math.max(0, ((now - start) / (end - start)) * 100));
}

function updateProgress() {
    progress.value = calculateProgress();
    if (progress.value > 0 && progress.value < 100) {
        if (interval.value === null) {
            interval.value = window.setInterval(updateProgress, 1000);
        }
    } else if (interval.value !== null) {
        clearInterval(interval.value);
        interval.value = null;
    }
}

onMounted(() => updateProgress());
onBeforeUnmount(() => {
    if (interval.value !== null) clearInterval(interval.value);
});

function refreshNextStop() {
    if (progress.value <= 0 || progress.value >= 100 || !props.stopovers?.length) {
        nextStop.value = null;
        isAtStop.value = false;
        return;
    }
    for (const stop of props.stopovers) {
        const depDiff = getDepartureForStopover(stop).dateTime.diffNow('seconds').seconds;
        if (depDiff > 0) {
            nextStop.value = stop;
            isAtStop.value = getArrivalForStopover(stop).dateTime.diffNow('seconds').seconds <= 0;
            return;
        }
    }
    nextStop.value = null;
}

watch(
    () => [props.stopovers, progress.value],
    () => refreshNextStop(),
    { immediate: true },
);

function escapeHtml(s: string): string {
    return s
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

const enrichedBody = computed(() => {
    const body = statusObject.value.body ?? '';
    const mentions: MentionDto[] =
        (statusObject.value as unknown as { bodyMentions?: MentionDto[] }).bodyMentions ?? [];

    if (!body) return '';
    if (!mentions.length) return escapeHtml(body);

    const userMap = new Map<string, UserResource>();
    for (const m of mentions) {
        if (m.user?.username) userMap.set(m.user.username.toLowerCase(), m.user);
    }
    if (!userMap.size) return escapeHtml(body);

    const mentionRegex = /@(\w+)/g;
    let result = '';
    let lastIndex = 0;
    let match: RegExpExecArray | null;
    while ((match = mentionRegex.exec(body)) !== null) {
        const user = userMap.get(match[1].toLowerCase());
        if (!user) continue;
        result += escapeHtml(body.slice(lastIndex, match.index));
        result += `<a href="/@${encodeURIComponent(user.username)}" class="link link-primary">${escapeHtml(match[0])}</a>`;
        lastIndex = match.index + match[0].length;
    }
    result += escapeHtml(body.slice(lastIndex));
    return result;
});

function toggleLike() {
    if (!userStore.user) return;
    if (statusObject.value.liked) {
        api.status.removeLikeFromStatus(statusObject.value.id).then(() => {
            statusObject.value.liked = false;
            likes.value--;
            emit('status-unliked');
        });
    } else {
        api.status.addLikeToStatus(statusObject.value.id).then(() => {
            statusObject.value.liked = true;
            likes.value++;
            emit('status-liked');
        });
    }
}

function onStatusUpdated(s: StatusResource) {
    statusObject.value = s;
    emit('status-updated', s);
}

function onStatusDeleted(id: number) {
    deleted.value = true;
    showDeleteModal.value = false;
    emit('status-deleted', id);
}

const visibilityIcon = computed(() => VISIBILITY_ICONS[statusObject.value.visibility] ?? Eye);
const inProgress = computed(() => progress.value > 0 && progress.value < 100);
</script>

<template>
    <Transition name="fade">
        <div v-if="!deleted" class="card bg-base-100 shadow-sm relative">
            <!-- Map (shown on status detail page) -->
            <div
                v-if="showMap"
                class="[&_canvas]:[border-top-left-radius:var(--rounded-box)] [&_canvas]:[border-top-right-radius:var(--rounded-box)]"
            >
                <TrwMap :statuses="[statusObject]" />
            </div>

            <div class="card-body p-4 pb-2">
                <!-- Journey timeline -->
                <div class="flex gap-2">
                    <!-- Left: continuous timeline -->
                    <div class="flex flex-col items-center shrink-0 pt-1">
                        <div class="w-3 h-3 rounded-full bg-primary shrink-0" />
                        <div class="w-0.5 bg-base-content/20 flex-1 my-1" />
                        <div class="w-3 h-3 rounded-full border-2 border-primary bg-base-100 shrink-0" />
                    </div>

                    <!-- Right: all content -->
                    <div class="min-w-0 flex-1 flex flex-col gap-3">
                        <!-- Origin -->
                        <div>
                            <div class="flex items-baseline justify-between gap-2">
                                <router-link
                                    :to="{
                                        name: 'stationboard',
                                        query: {
                                            stationId: statusObject.checkin.origin.id,
                                            stationName: statusObject.checkin.origin.name,
                                        },
                                    }"
                                    class="font-medium link link-hover leading-tight"
                                    :class="{ 'line-through text-error': statusObject.checkin.origin.cancelled }"
                                >
                                    {{ statusObject.checkin.origin.name }}
                                    <span
                                        v-if="statusObject.checkin.origin.cancelled"
                                        class="badge badge-error badge-xs ml-1"
                                    >
                                        {{ trans('stationboard.stop-cancelled') }}
                                    </span>
                                </router-link>
                                <div class="text-sm tabular-nums shrink-0 text-right">
                                    <span v-if="departure.originalTime" class="line-through text-base-content/40 mr-1">
                                        {{ fmtTime(departure.originalTime) }}
                                    </span>
                                    <span
                                        class="tooltip tooltip-left"
                                        :data-tip="trans(timeTypeTooltip(departure.type))"
                                        :class="delayClass(departure)"
                                    >
                                        {{ fmtTime(departure.time) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Line info row -->
                            <div class="flex flex-wrap items-center gap-1.5 mt-1 text-sm text-base-content/60">
                                <span class="inline-flex items-center w-4 h-4 [&_img]:w-4 [&_img]:h-4 [&_i]:text-base">
                                    <ProductIcon
                                        :mode="statusObject.checkin.mode"
                                        :product="statusObject.checkin.category"
                                    />
                                </span>
                                <LineIndicator
                                    class-name="line-indicator line-badge align-middle"
                                    :product-name="statusObject.checkin.category"
                                    :number="statusObject.checkin.lineName"
                                    :mode="statusObject.checkin.mode"
                                    :color="statusObject.checkin.routeTextColor || undefined"
                                    :background-color="statusObject.checkin.routeColor || undefined"
                                />
                                <span v-if="statusObject.checkin.manualJourneyNumber" class="opacity-60">
                                    ({{ statusObject.checkin.manualJourneyNumber }})
                                </span>
                                <span
                                    v-else-if="
                                        statusObject.checkin.journeyNumber &&
                                        !statusObject.checkin.lineName.includes(
                                            statusObject.checkin.journeyNumber.toString(),
                                        )
                                    "
                                    class="opacity-60"
                                >
                                    ({{ statusObject.checkin.journeyNumber }})
                                </span>
                                <span class="flex items-center gap-0.5">
                                    <Route class="inline-block size-4" />
                                    <DistanceSpan :distance="statusObject.checkin.distance" />
                                </span>
                                <span v-if="statusObject.checkin.duration" class="flex items-center gap-0.5">
                                    <Clock class="inline-block size-4" />
                                    <DurationSpan :duration="statusObject.checkin.duration" />
                                </span>
                                <span
                                    v-if="statusObject.business === Business.Value1"
                                    class="tooltip"
                                    :data-tip="trans('stationboard.business.business')"
                                >
                                    <Briefcase class="inline-block size-4" />
                                </span>
                                <span
                                    v-else-if="statusObject.business === Business.Value2"
                                    class="tooltip"
                                    :data-tip="trans('stationboard.business.commute')"
                                >
                                    <Building2 class="inline-block size-4" />
                                </span>
                                <span v-if="statusObject.event" class="flex items-center gap-0.5">
                                    <Calendar class="inline-block size-4" />
                                    <a :href="`/event/${statusObject.event.slug}`" class="link link-hover">
                                        {{ statusObject.event.name }}
                                    </a>
                                </span>
                            </div>
                        </div>

                        <!-- Status body -->
                        <!-- eslint-disable-next-line vue/no-v-html -->
                        <p
                            v-if="statusObject.body"
                            class="text-base text-base-content/80 italic whitespace-pre-wrap break-words [font-synthesis:none]"
                            v-html="enrichedBody"
                        />

                        <!-- Moderation notices -->
                        <div
                            v-if="
                                statusObject.moderation_notes || statusObject.lock_visibility || statusObject.hide_body
                            "
                        >
                            <div class="alert alert-error py-1 px-2 text-xs flex flex-col gap-1">
                                <div v-if="statusObject.moderation_notes" class="flex items-center gap-1">
                                    <AlertTriangle class="w-3 h-3 shrink-0" />
                                    {{ statusObject.moderation_notes }}
                                </div>
                                <div v-if="statusObject.lock_visibility" class="flex items-center gap-1">
                                    <Lock class="w-3 h-3 shrink-0" />
                                    {{ trans('status.locked-visibility') }}
                                </div>
                                <div v-if="statusObject.hide_body" class="flex items-center gap-1">
                                    <EyeOff class="w-3 h-3 shrink-0" />
                                    {{ trans('status.hidden-body') }}
                                </div>
                            </div>
                        </div>

                        <!-- Next stop -->
                        <p v-if="nextStop && inProgress" class="text-sm text-base-content/50 flex items-center gap-1">
                            <ArrowRight class="w-3 h-3 shrink-0" />
                            {{ isAtStop ? trans('stationboard.current-stop') : trans('stationboard.next-stop') }}
                            <router-link
                                :to="{
                                    name: 'stationboard',
                                    query: { stationId: nextStop.id, stationName: nextStop.name },
                                }"
                                class="link link-hover font-medium"
                            >
                                {{ nextStop.name }}
                                ({{ fmtTime(getArrivalForStopover(nextStop)) }})
                            </router-link>
                        </p>

                        <!-- Destination -->
                        <div>
                            <div class="flex items-baseline justify-between gap-2">
                                <router-link
                                    :to="{
                                        name: 'stationboard',
                                        query: {
                                            stationId: statusObject.checkin.destination.id,
                                            stationName: statusObject.checkin.destination.name,
                                        },
                                    }"
                                    class="font-medium link link-hover leading-tight"
                                    :class="{ 'line-through text-error': statusObject.checkin.destination.cancelled }"
                                >
                                    {{ statusObject.checkin.destination.name }}
                                    <span
                                        v-if="statusObject.checkin.destination.cancelled"
                                        class="badge badge-error badge-xs ml-1"
                                    >
                                        {{ trans('stationboard.stop-cancelled') }}
                                    </span>
                                </router-link>
                                <div class="text-sm tabular-nums shrink-0 text-right">
                                    <span v-if="arrival.originalTime" class="line-through text-base-content/40 mr-1">
                                        {{ fmtTime(arrival.originalTime) }}
                                    </span>
                                    <span
                                        class="tooltip tooltip-left"
                                        :data-tip="trans(timeTypeTooltip(arrival.type))"
                                        :class="delayClass(arrival)"
                                    >
                                        {{ fmtTime(arrival.time) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- progress bar -->
            <div class="w-full h-1 bg-base-200">
                <div
                    class="h-1 bg-primary transition-all duration-1000"
                    :class="{
                        'bg-gradient-to-r from-pink-400 via-purple-400 to-blue-400': statusObject.event?.isPride,
                    }"
                    :style="{ width: `${progress}%` }"
                />
            </div>

            <!-- Footer: user info + actions -->
            <div class="px-4 py-2 flex items-center gap-2 text-sm border-t border-base-200">
                <!-- User avatar -->
                <a :href="`/@${statusObject.user.username}`" class="shrink-0">
                    <img
                        :src="statusObject.user.profilePicture"
                        :alt="statusObject.user.username"
                        class="w-7 h-7 rounded-full object-cover"
                        loading="lazy"
                    />
                </a>

                <!-- Name + timestamp -->
                <div class="min-w-0 flex-1 flex items-center gap-2">
                    <a :href="`/@${statusObject.user.username}`" class="font-medium text-xs link link-hover">
                        {{
                            userStore.user?.id === statusObject.user.id ? trans('user.you') : statusObject.user.username
                        }}
                    </a>
                    <a :href="`/status/${statusObject.id}`" class="text-xs text-base-content/40 link link-hover">
                        {{ new Dtm(statusObject.createdAt).toRelative() }}
                    </a>
                </div>

                <!-- Like -->
                <button
                    v-if="statusObject.isLikable"
                    class="flex items-center gap-1 text-xs text-base-content/40 hover:text-error transition-colors"
                    :class="{ 'text-error': statusObject.liked }"
                    @click="toggleLike"
                >
                    <Heart
                        class="inline-block size-4"
                        :class="statusObject.liked ? 'fill-error stroke-error' : 'stroke-current'"
                    />
                    <span v-if="likes > 0">{{ likes }}</span>
                </button>

                <!-- Visibility icon -->
                <span class="tooltip" :data-tip="trans('status.visibility.' + statusObject.visibility)">
                    <component :is="visibilityIcon" class="w-4 h-4 text-base-content/30 shrink-0" />
                </span>

                <!-- Context menu -->
                <StatusContextMenu
                    :status="statusObject"
                    @edit="showEditModal = true"
                    @delete="showDeleteModal = true"
                    @status-updated="onStatusUpdated"
                />
            </div>
        </div>
    </Transition>

    <StatusEditModal
        :open="showEditModal"
        :status="statusObject"
        @close="showEditModal = false"
        @saved="
            (s) => {
                statusObject = s;
                showEditModal = false;
                emit('status-updated', s);
            }
        "
    />

    <StatusDeleteModal
        :open="showDeleteModal"
        :status-id="statusObject.id"
        @close="showDeleteModal = false"
        @deleted="onStatusDeleted"
    />
</template>

<style scoped>
.fade-leave-active {
    transition: opacity 0.4s ease;
}
.fade-leave-to {
    opacity: 0;
}
</style>
