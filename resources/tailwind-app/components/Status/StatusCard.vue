<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import {
    AlertTriangle,
    ArrowRight,
    Briefcase,
    Building2,
    Calendar,
    Clock,
    Eye,
    EyeOff,
    Globe,
    Heart,
    Lock,
    MoreVertical,
    PlaneLanding,
    PlaneTakeoff,
    Route,
    Share2,
    Shield,
    Trash2,
    User,
    UserCheck,
    UserPlus,
    UserX,
    VolumeX,
} from 'lucide-vue-next';
import { DateTime as LuxonDateTime } from 'luxon';
import { Notyf } from 'notyf';
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import {
    Api,
    Business,
    EventResource,
    MentionDto,
    StatusResource,
    StatusUpdateBody,
    StatusVisibility,
    StopoverResource,
    UserResource,
} from '../../../types/Api.gen';
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
} from '../../../vue/helpers/DateTimeHelper';
import { useActiveCheckin } from '../../../vue/stores/activeCheckin';
import { useUserStore } from '../../../vue/stores/user';

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
const notyf = inject('notyf') as Notyf;
const userStore = useUserStore();
const activeCheckin = useActiveCheckin();

const statusObject = ref<StatusResource>(props.status);
const deleted = ref(false);
const deleting = ref(false);
const progress = ref(0);
const interval = ref<number | null>(null);
const likes = ref(props.status.likes ?? 0);

// next stop logic
const nextStop = ref<StopoverResource | null>(null);
const isAtStop = ref(false);

// modals state
const showDeleteConfirm = ref(false);
const busyMute = ref(false);
const busyBlock = ref(false);
const busyDepartureNow = ref(false);
const busyArrivalNow = ref(false);

// edit modal state
const showEditModal = ref(false);
const editLoading = ref(false);
const editStopovers = ref<StopoverResource[]>([]);
const editDestinationValue = ref<string | null>(null);
const editManualDeparture = ref<string>('');
const editManualArrival = ref<string>('');
const editBody = ref('');
const editBusiness = ref<Business>(Business.Value0);
const editVisibility = ref<StatusVisibility>(StatusVisibility.Value0);
const editEventId = ref<number | null>(null);
const editEvents = ref<EventResource[]>([]);

function isoToDatetimeLocal(iso: string | null | undefined): string {
    if (!iso) return '';
    return LuxonDateTime.fromISO(iso).toFormat("yyyy-MM-dd'T'HH:mm");
}

async function openEditModal() {
    editBody.value = statusObject.value.body ?? '';
    editBusiness.value = statusObject.value.business ?? Business.Value0;
    editVisibility.value = statusObject.value.visibility ?? StatusVisibility.Value0;
    editEventId.value = statusObject.value.event?.id ?? null;
    editManualDeparture.value = isoToDatetimeLocal(statusObject.value.train.manualDeparture);
    editManualArrival.value = isoToDatetimeLocal(statusObject.value.train.manualArrival);
    editDestinationValue.value = null;
    editStopovers.value = [];
    showEditModal.value = true;

    try {
        const res = await api.trains.getTrainTrip({
            hafasTripId: statusObject.value.train.trip,
            lineName: statusObject.value.train.lineName,
            start: statusObject.value.train.origin.id,
        });
        const all: StopoverResource[] = res.data?.data?.stopovers ?? [];
        const depPlanned = LuxonDateTime.fromISO(statusObject.value.train.origin.departurePlanned ?? '');
        editStopovers.value = all.filter((s) => {
            const arr = LuxonDateTime.fromISO(s.arrivalPlanned ?? s.arrival ?? s.departurePlanned ?? s.departure ?? '');
            return arr.isValid && depPlanned.isValid && arr.diff(depPlanned).as('minutes') >= 0;
        });
        const cur = editStopovers.value.find(
            (s) =>
                s.id === statusObject.value.train.destination.id &&
                s.arrivalPlanned === statusObject.value.train.destination.arrivalPlanned,
        );
        if (cur?.arrivalPlanned) {
            editDestinationValue.value = `${cur.id}|${cur.arrivalPlanned}`;
        }
    } catch {
        // stopovers best-effort
    }

    try {
        const dep = getDepartureForStatus(statusObject.value).toISO() ?? '';
        const evRes = await api.events.getEvents({ timestamp: dep });
        editEvents.value = evRes.data.data ?? [];
    } catch {
        editEvents.value = [];
    }
}

function closeDropdown() {
    (document.activeElement as HTMLElement)?.blur();
}

async function saveEdit() {
    editLoading.value = true;
    try {
        const body: StatusUpdateBody = {
            body: editBody.value || null,
            business: editBusiness.value,
            visibility: editVisibility.value,
            eventId: editEventId.value,
            manualDeparture: editManualDeparture.value
                ? LuxonDateTime.fromISO(editManualDeparture.value).toISO()
                : null,
            manualArrival: editManualArrival.value ? LuxonDateTime.fromISO(editManualArrival.value).toISO() : null,
        } as StatusUpdateBody;

        if (editDestinationValue.value) {
            const idx = editDestinationValue.value.indexOf('|');
            body.destinationId = Number(editDestinationValue.value.slice(0, idx));
            body.destinationArrivalPlanned = editDestinationValue.value.slice(idx + 1);
        }

        const res = await api.status.updateSingleStatus(body, statusObject.value.id);
        statusObject.value = res.data.data as StatusResource;
        emit('status-updated', statusObject.value);
        showEditModal.value = false;
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        editLoading.value = false;
    }
}

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
    if (!isFinite(start) || !isFinite(end) || end <= start) return now >= end ? 100 : 0;
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

const enrichedBody = computed(() => {
    const body = statusObject.value.body ?? '';
    const mentions: MentionDto[] =
        (statusObject.value as unknown as { bodyMentions?: MentionDto[] }).bodyMentions ?? [];

    function escapeHtml(s: string): string {
        return s
            .replaceAll(/&/g, '&amp;')
            .replaceAll(/</g, '&lt;')
            .replaceAll(/>/g, '&gt;')
            .replaceAll(/"/g, '&quot;')
            .replaceAll(/'/g, '&#039;');
    }

    if (!body) return '';
    if (!mentions.length) return escapeHtml(body);

    const userMap = new Map<string, UserResource>();
    for (const m of mentions) {
        if (m.user?.username) userMap.set(m.user.username.toLowerCase(), m.user);
    }
    if (!userMap.size) return escapeHtml(body);

    const mentionRegex = /@([a-zA-Z0-9_]+)/g;
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

function confirmDelete() {
    showDeleteConfirm.value = true;
}

function executeDelete() {
    deleting.value = true;
    showDeleteConfirm.value = false;
    api.status
        .destroySingleStatus(statusObject.value.id)
        .then(() => {
            deleted.value = true;
            emit('status-deleted', statusObject.value.id);
            if (activeCheckin.status?.id === statusObject.value.id) {
                activeCheckin.reset();
            }
        })
        .catch((e) => {
            deleting.value = false;
            notyf?.error(String(e));
        });
}

function getNowIso(): string {
    return LuxonDateTime.now()
        .set({ second: 0, millisecond: 0 })
        .toISO({ suppressSeconds: true, suppressMilliseconds: true });
}

async function departureNow() {
    busyDepartureNow.value = true;
    try {
        const res = await api.status.updateSingleStatus(
            { manualDeparture: getNowIso() } as never,
            statusObject.value.id,
        );
        statusObject.value = res.data.data as StatusResource;
        emit('status-updated', statusObject.value);
    } finally {
        busyDepartureNow.value = false;
    }
}

async function arrivalNow() {
    busyArrivalNow.value = true;
    try {
        const res = await api.status.updateSingleStatus({ manualArrival: getNowIso() } as never, statusObject.value.id);
        statusObject.value = res.data.data as StatusResource;
        emit('status-updated', statusObject.value);
    } finally {
        busyArrivalNow.value = false;
    }
}

function share() {
    const url = `${window.location.origin}/status/${statusObject.value.id}`;
    const origin = statusObject.value.train.origin.name;
    const dest = statusObject.value.train.destination.name;
    const text = statusObject.value.body
        ? `${statusObject.value.body} (@ ${statusObject.value.train.lineName} ${origin} -> ${dest}) #NowTräwelling`
        : `${statusObject.value.train.lineName} ${origin} -> ${dest} #NowTräwelling`;

    if (navigator.share) {
        navigator.share({ title: 'Träwelling', text, url }).catch(() => {});
    } else {
        navigator.clipboard.writeText(`${text} ${url}`).then(() => {
            notyf?.success(trans('menu.share.clipboard.success'));
        });
    }
}

function rideAlongRoute() {
    const t = statusObject.value.train;
    return {
        name: 'checkin',
        query: {
            tripId: t.trip.toString(),
            lineName: t.lineName,
            start: t.origin.id.toString(),
            destination: t.destination.id.toString(),
            departure: t.origin.departurePlanned ?? t.origin.departure ?? '',
            originName: t.origin.name,
            destinationName: t.destination.name,
            category: t.category,
        },
    };
}

async function handleMute() {
    busyMute.value = true;
    try {
        await api.user.createMute(statusObject.value.userDetails.id as unknown as number);
        notyf?.success(trans('user.muted', { username: statusObject.value.userDetails.username }));
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        busyMute.value = false;
    }
}

async function handleBlock() {
    busyBlock.value = true;
    try {
        const id = statusObject.value.userDetails.id;
        await api.user.createBlock(String(id), { userId: id });
        notyf?.success(trans('user.blocked', { username: statusObject.value.userDetails.username }));
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        busyBlock.value = false;
    }
}

const visibilityIcon = computed(() => {
    switch (statusObject.value.visibility) {
        case StatusVisibility.Value0:
            return Globe;
        case StatusVisibility.Value1:
            return Lock;
        case StatusVisibility.Value2:
            return UserCheck;
        case StatusVisibility.Value3:
            return Lock;
        case StatusVisibility.Value4:
            return UserCheck;
        case StatusVisibility.Value5:
            return Shield;
        default:
            return Eye;
    }
});

const isOwn = computed(() => !!userStore.user && userStore.user.id === statusObject.value.userDetails.id);
const isBusy = computed(() => deleting.value);
const inProgress = computed(() => progress.value > 0 && progress.value < 100);

const showDepartureNowBtn = computed(() => {
    if (!isOwn.value) return false;
    const origin = statusObject.value.train.origin;
    const dest = statusObject.value.train.destination;
    const dep = LuxonDateTime.fromISO(origin.departurePlanned ?? origin.departure ?? '');
    const arr = LuxonDateTime.fromISO(dest.arrivalPlanned ?? dest.arrival ?? '');
    const now = LuxonDateTime.now();
    return dep.isValid && arr.isValid && now >= dep.minus({ minutes: 60 }) && now <= arr.plus({ days: 1 });
});

const showArrivalNowBtn = computed(() => {
    if (!isOwn.value) return false;
    const origin = statusObject.value.train.origin;
    const dest = statusObject.value.train.destination;
    const dep = LuxonDateTime.fromISO(origin.departurePlanned ?? origin.departure ?? '');
    const arr = LuxonDateTime.fromISO(dest.arrivalPlanned ?? dest.arrival ?? '');
    const now = LuxonDateTime.now();
    return dep.isValid && arr.isValid && now >= dep && now <= arr.plus({ days: 1 });
});

const distanceStr = computed(() => {
    const d = statusObject.value.train.distance;
    return d < 1000 ? `${d} m` : `${(d / 1000).toFixed(0)} km`;
});

const durationStr = computed(() => {
    const total = statusObject.value.train.duration ?? 0;
    if (total <= 0) return null;
    const days = Math.floor(total / (60 * 24));
    const hours = Math.floor((total % (60 * 24)) / 60);
    const mins = total % 60;
    if (days > 0) return `${days}d ${hours}h ${mins}min`;
    if (hours > 0) return `${hours}h ${mins}min`;
    return `${mins}min`;
});
</script>

<template>
    <Transition name="fade">
        <div v-if="!deleted" class="card bg-base-100 shadow-sm relative">
            <!-- deleting overlay -->
            <Transition name="overlay">
                <div
                    v-if="isBusy"
                    class="absolute inset-0 z-10 bg-base-100/70 flex items-center justify-center rounded-box"
                >
                    <span class="loading loading-spinner loading-md" />
                </div>
            </Transition>

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
                                            stationId: statusObject.train.origin.id,
                                            stationName: statusObject.train.origin.name,
                                        },
                                    }"
                                    class="font-medium link link-hover leading-tight"
                                    :class="{ 'line-through text-error': statusObject.train.origin.cancelled }"
                                >
                                    {{ statusObject.train.origin.name }}
                                    <span
                                        v-if="statusObject.train.origin.cancelled"
                                        class="badge badge-error badge-xs ml-1"
                                    >
                                        {{ trans('stationboard.stop-cancelled') }}
                                    </span>
                                </router-link>
                                <div class="text-sm tabular-nums shrink-0 text-right">
                                    <span v-if="departure.originalTime" class="line-through text-base-content/40 mr-1">
                                        {{ fmtTime(departure.originalTime) }}
                                    </span>
                                    <span :class="delayClass(departure)">
                                        {{ fmtTime(departure.time) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Line info row -->
                            <div class="flex flex-wrap items-center gap-1.5 mt-1 text-sm text-base-content/60">
                                <span class="inline-flex items-center w-4 h-4 [&_img]:w-4 [&_img]:h-4 [&_i]:text-base">
                                    <ProductIcon
                                        :mode="statusObject.train.mode"
                                        :product="statusObject.train.category"
                                    />
                                </span>
                                <LineIndicator
                                    class-name="line-indicator line-badge align-middle"
                                    :product-name="statusObject.train.category"
                                    :number="statusObject.train.lineName"
                                    :mode="statusObject.train.mode"
                                    :color="statusObject.train.routeTextColor"
                                    :background-color="statusObject.train.routeColor"
                                />
                                <span v-if="statusObject.train.manualJourneyNumber" class="opacity-60">
                                    ({{ statusObject.train.manualJourneyNumber }})
                                </span>
                                <span
                                    v-else-if="
                                        statusObject.train.journeyNumber &&
                                        !statusObject.train.lineName.includes(
                                            statusObject.train.journeyNumber.toString(),
                                        )
                                    "
                                    class="opacity-60"
                                >
                                    ({{ statusObject.train.journeyNumber }})
                                </span>
                                <span class="flex items-center gap-0.5">
                                    <Route class="w-4 h-4" />
                                    {{ distanceStr }}
                                </span>
                                <span v-if="durationStr" class="flex items-center gap-0.5">
                                    <Clock class="w-4 h-4" />
                                    {{ durationStr }}
                                </span>
                                <span
                                    v-if="statusObject.business === Business.Value1"
                                    class="tooltip"
                                    :data-tip="trans('stationboard.business.business')"
                                >
                                    <Briefcase class="w-4 h-4" />
                                </span>
                                <span
                                    v-else-if="statusObject.business === Business.Value2"
                                    class="tooltip"
                                    :data-tip="trans('stationboard.business.commute')"
                                >
                                    <Building2 class="w-4 h-4" />
                                </span>
                                <span v-if="statusObject.event" class="flex items-center gap-0.5">
                                    <Calendar class="w-4 h-4" />
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
                            class="text-base text-base-content/80 italic whitespace-pre-wrap break-words"
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
                                            stationId: statusObject.train.destination.id,
                                            stationName: statusObject.train.destination.name,
                                        },
                                    }"
                                    class="font-medium link link-hover leading-tight"
                                    :class="{ 'line-through text-error': statusObject.train.destination.cancelled }"
                                >
                                    {{ statusObject.train.destination.name }}
                                    <span
                                        v-if="statusObject.train.destination.cancelled"
                                        class="badge badge-error badge-xs ml-1"
                                    >
                                        {{ trans('stationboard.stop-cancelled') }}
                                    </span>
                                </router-link>
                                <div class="text-sm tabular-nums shrink-0 text-right">
                                    <span v-if="arrival.originalTime" class="line-through text-base-content/40 mr-1">
                                        {{ fmtTime(arrival.originalTime) }}
                                    </span>
                                    <span :class="delayClass(arrival)">
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
                <!-- User avatar (mobile) -->
                <a :href="`/@${statusObject.userDetails.username}`" class="shrink-0">
                    <img
                        :src="statusObject.userDetails.profilePicture"
                        :alt="statusObject.userDetails.username"
                        class="w-7 h-7 rounded-full object-cover"
                        loading="lazy"
                    />
                </a>

                <!-- Name + timestamp -->
                <div class="min-w-0 flex-1 flex items-center gap-2">
                    <a :href="`/@${statusObject.userDetails.username}`" class="font-medium text-xs link link-hover">
                        {{ isOwn ? trans('user.you') : statusObject.userDetails.username }}
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
                    <Heart class="w-4 h-4" :class="statusObject.liked ? 'fill-error stroke-error' : 'stroke-current'" />
                    <span v-if="likes > 0">{{ likes }}</span>
                </button>

                <!-- Visibility icon -->
                <span class="tooltip" :data-tip="trans('status.visibility.' + statusObject.visibility)">
                    <component :is="visibilityIcon" class="w-4 h-4 text-base-content/30 shrink-0" />
                </span>

                <!-- Context menu -->
                <div class="dropdown dropdown-end">
                    <button tabindex="0" class="btn btn-ghost btn-xs btn-circle text-base-content/40">
                        <MoreVertical class="w-4 h-4" />
                    </button>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-base-100 rounded-box z-20 w-48 p-1 shadow-lg border border-base-200"
                    >
                        <li>
                            <button @click="share">
                                <Share2 class="w-4 h-4" />
                                {{ trans('menu.share') }}
                            </button>
                        </li>

                        <template v-if="userStore.user">
                            <template v-if="isOwn">
                                <li v-if="showDepartureNowBtn">
                                    <button :disabled="busyDepartureNow" @click="departureNow">
                                        <PlaneTakeoff class="w-4 h-4" />
                                        {{ trans('status.departure-now') }}
                                    </button>
                                </li>
                                <li v-if="showArrivalNowBtn">
                                    <button :disabled="busyArrivalNow" @click="arrivalNow">
                                        <PlaneLanding class="w-4 h-4" />
                                        {{ trans('status.arrival-now') }}
                                    </button>
                                </li>
                                <li>
                                    <button @click="openEditModal">
                                        <Eye class="w-4 h-4" />
                                        {{ trans('edit') }}
                                    </button>
                                </li>
                                <li>
                                    <button class="text-error" @click="confirmDelete">
                                        <Trash2 class="w-4 h-4" />
                                        {{ trans('delete') }}
                                    </button>
                                </li>
                            </template>
                            <template v-else>
                                <li>
                                    <router-link :to="rideAlongRoute()">
                                        <UserPlus class="w-4 h-4" />
                                        {{ trans('status.join') }}
                                    </router-link>
                                </li>
                                <li>
                                    <button :disabled="busyMute" @click="handleMute">
                                        <VolumeX class="w-4 h-4" />
                                        {{ trans('user.mute-tooltip') }}
                                    </button>
                                </li>
                                <li>
                                    <button class="text-error" :disabled="busyBlock" @click="handleBlock">
                                        <UserX class="w-4 h-4" />
                                        {{ trans('user.block-tooltip') }}
                                    </button>
                                </li>
                            </template>
                            <template v-if="userStore.isAdmin">
                                <li class="menu-title text-xs">Admin</li>
                                <li>
                                    <a :href="`/admin/statuses/${statusObject.id}`">
                                        {{ trans('menu.backend') }}
                                    </a>
                                </li>
                            </template>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </Transition>

    <!-- edit modal -->
    <dialog class="modal" :class="{ 'modal-open': showEditModal }">
        <div class="modal-box w-11/12 max-w-xl overflow-visible">
            <h3 class="font-bold text-lg mb-4">{{ trans('modals.editStatus-title') }}</h3>

            <!-- Destination -->
            <div class="form-control mb-3">
                <label class="label"
                    ><span class="label-text">{{ trans('exit') }}</span></label
                >
                <select v-model="editDestinationValue" class="select select-bordered w-full">
                    <option
                        v-for="s in editStopovers"
                        :key="`${s.id}-${s.arrivalPlanned}`"
                        :value="`${s.id}|${s.arrivalPlanned}`"
                    >
                        {{ LuxonDateTime.fromISO(s.arrivalPlanned ?? s.arrival ?? '').toFormat('HH:mm') }}: {{ s.name }}
                    </option>
                </select>
            </div>

            <!-- Manual times -->
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="form-control">
                    <label class="label"
                        ><span class="label-text text-xs">{{ trans('export.title.departure_real') }}</span></label
                    >
                    <input v-model="editManualDeparture" type="datetime-local" class="input input-bordered input-sm" />
                </div>
                <div class="form-control">
                    <label class="label"
                        ><span class="label-text text-xs">{{ trans('export.title.arrival_real') }}</span></label
                    >
                    <input v-model="editManualArrival" type="datetime-local" class="input input-bordered input-sm" />
                </div>
            </div>

            <!-- Body -->
            <div class="form-control mb-3">
                <textarea
                    v-model="editBody"
                    class="textarea textarea-bordered"
                    :placeholder="trans('modals.editStatus-label')"
                    maxlength="280"
                    rows="4"
                />
                <div v-if="(editBody || '').length > 100" class="label">
                    <span class="label-text-alt text-base-content/50">{{ (editBody || '').length }}/280</span>
                </div>
            </div>

            <!-- Business, Visibility, Event -->
            <div class="flex flex-wrap gap-2 mb-4">
                <!-- Business dropdown -->
                <div class="dropdown">
                    <button tabindex="0" class="btn btn-sm btn-outline gap-1">
                        <User v-if="editBusiness === Business.Value0" class="w-4 h-4" />
                        <Briefcase v-else-if="editBusiness === Business.Value1" class="w-4 h-4" />
                        <Building2 v-else class="w-4 h-4" />
                        {{
                            editBusiness === Business.Value0
                                ? trans('stationboard.business.private')
                                : editBusiness === Business.Value1
                                  ? trans('stationboard.business.business')
                                  : trans('stationboard.business.commute')
                        }}
                    </button>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-base-100 rounded-box z-10 w-64 p-2 shadow-lg border border-base-200"
                    >
                        <li
                            @click="
                                editBusiness = Business.Value0;
                                closeDropdown();
                            "
                        >
                            <a :class="editBusiness === Business.Value0 ? 'active' : ''">
                                <User class="w-4 h-4 shrink-0" />
                                <span>
                                    {{ trans('stationboard.business.private') }}
                                </span>
                            </a>
                        </li>
                        <li
                            @click="
                                editBusiness = Business.Value1;
                                closeDropdown();
                            "
                        >
                            <a :class="editBusiness === Business.Value1 ? 'active' : ''">
                                <Briefcase class="w-4 h-4 shrink-0" />
                                <span>
                                    {{ trans('stationboard.business.business') }}
                                    <span class="block text-xs text-base-content/50">{{
                                        trans('stationboard.business.business.detail')
                                    }}</span>
                                </span>
                            </a>
                        </li>
                        <li
                            @click="
                                editBusiness = Business.Value2;
                                closeDropdown();
                            "
                        >
                            <a :class="editBusiness === Business.Value2 ? 'active' : ''">
                                <Building2 class="w-4 h-4 shrink-0" />
                                <span>
                                    {{ trans('stationboard.business.commute') }}
                                    <span class="block text-xs text-base-content/50">{{
                                        trans('stationboard.business.commute.detail')
                                    }}</span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Visibility dropdown -->
                <div v-if="!statusObject.lock_visibility" class="dropdown">
                    <button tabindex="0" class="btn btn-sm btn-outline gap-1">
                        <Globe v-if="editVisibility === StatusVisibility.Value0" class="w-4 h-4" />
                        <Eye v-else-if="editVisibility === StatusVisibility.Value1" class="w-4 h-4" />
                        <UserCheck v-else-if="editVisibility === StatusVisibility.Value2" class="w-4 h-4" />
                        <Lock v-else-if="editVisibility === StatusVisibility.Value3" class="w-4 h-4" />
                        <UserCheck v-else-if="editVisibility === StatusVisibility.Value4" class="w-4 h-4" />
                        <Shield v-else class="w-4 h-4" />
                        {{ trans('status.visibility.' + editVisibility) }}
                    </button>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-base-100 rounded-box z-10 w-72 p-2 shadow-lg border border-base-200"
                    >
                        <li
                            v-for="v in [0, 1, 2, 3, 4, 5]"
                            :key="v"
                            @click="
                                editVisibility = v as StatusVisibility;
                                closeDropdown();
                            "
                        >
                            <a :class="editVisibility === v ? 'active' : ''">
                                <Globe v-if="v === 0" class="w-4 h-4 shrink-0" />
                                <Eye v-else-if="v === 1" class="w-4 h-4 shrink-0" />
                                <UserCheck v-else-if="v === 2" class="w-4 h-4 shrink-0" />
                                <Lock v-else-if="v === 3" class="w-4 h-4 shrink-0" />
                                <UserCheck v-else-if="v === 4" class="w-4 h-4 shrink-0" />
                                <Shield v-else class="w-4 h-4 shrink-0" />
                                <span>
                                    {{ trans('status.visibility.' + v) }}
                                    <span class="block text-xs text-base-content/50">{{
                                        trans('status.visibility.' + v + '.detail')
                                    }}</span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Event dropdown -->
                <div class="dropdown">
                    <button tabindex="0" class="btn btn-sm btn-outline gap-1">
                        <Calendar class="w-4 h-4" />
                        {{
                            editEventId
                                ? (editEvents.find((e) => e.id === editEventId)?.name ?? '…')
                                : trans('events.no-event-dropdown')
                        }}
                    </button>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-base-100 rounded-box z-10 w-72 p-2 shadow-lg border border-base-200 max-h-60 overflow-y-auto"
                    >
                        <li
                            @click="
                                editEventId = null;
                                closeDropdown();
                            "
                        >
                            <a :class="editEventId === null ? 'active' : ''">
                                <Calendar class="w-4 h-4 shrink-0" />
                                {{ trans('events.no-event-dropdown') }}
                            </a>
                        </li>
                        <li
                            v-for="e in editEvents"
                            :key="e.id"
                            @click="
                                editEventId = e.id;
                                closeDropdown();
                            "
                        >
                            <a :class="editEventId === e.id ? 'active' : ''">
                                <Calendar class="w-4 h-4 shrink-0" />
                                <span>
                                    {{ e.name }}
                                    <span v-if="e.hashtag" class="block text-xs text-base-content/50"
                                        >#{{ e.hashtag }}</span
                                    >
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="modal-action">
                <button class="btn btn-ghost" @click="showEditModal = false">{{ trans('cancel') }}</button>
                <button class="btn btn-primary" :disabled="editLoading" @click="saveEdit">
                    <span v-if="editLoading" class="loading loading-spinner loading-xs" />
                    {{ trans('modals.edit-confirm') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @submit.prevent="showEditModal = false">
            <button>close</button>
        </form>
    </dialog>

    <!-- delete confirm dialog -->
    <dialog class="modal" :class="{ 'modal-open': showDeleteConfirm }">
        <div class="modal-box">
            <h3 class="font-bold text-lg">{{ trans('modals.deleteStatus-title') }}</h3>
            <div class="modal-action">
                <button class="btn btn-ghost" @click="showDeleteConfirm = false">
                    {{ trans('cancel') }}
                </button>
                <button class="btn btn-error" @click="executeDelete">
                    {{ trans('delete') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @submit.prevent="showDeleteConfirm = false">
            <button>close</button>
        </form>
    </dialog>
</template>

<style scoped>
.fade-leave-active {
    transition: opacity 0.4s ease;
}
.fade-leave-to {
    opacity: 0;
}
.overlay-enter-active,
.overlay-leave-active {
    transition: opacity 0.15s ease;
}
.overlay-enter-from,
.overlay-leave-to {
    opacity: 0;
}
</style>
