<script setup lang="ts">
import { ArrowLeft, ChevronDown, ChevronUp, Plus, TriangleAlert } from '@lucide/vue';
import { getActiveLanguage, trans, transChoice } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Api, DepartureResource, Station, StopoverResource, TravelType } from '../../../types/Api.gen';
import LineIndicator from '../../../vue/components/LineIndicator.vue';
import { useUserStore } from '../../../vue/stores/user';
import CheckinForm from '../../components/Checkin/CheckinForm.vue';
import LineRun from '../../components/Checkin/LineRun.vue';
import DepartureEntry from '../../components/Stationboard/DepartureEntry.vue';
import HomeStationToggle from '../../components/Stationboard/HomeStationToggle.vue';
import StationTechnicalDetails from '../../components/Stationboard/StationTechnicalDetails.vue';
import StationSearch from '../../components/StationSearch.vue';
import TransportIcon from '../../components/TransportIcon.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();
const router = useRouter();
const notyf = inject('notyf') as Notyf;
const userStore = useUserStore();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

type DepartureMeta = {
    station?: Station;
    times?: { prev: string; now: string; next: string };
    removedLicenses?: { licenseName?: string }[];
};

const departures = ref<DepartureResource[]>([]);
const meta = ref<DepartureMeta>({});
const fetchTime = ref<DateTime>(DateTime.now().setZone('UTC'));
const loading = ref(false);
const travelType = ref('');

// Modal state
const selectedTrain = ref<DepartureResource | null>(null);
const selectedDestination = ref<StopoverResource | null>(null);
const modalRef = ref<HTMLDialogElement | null>(null);

const stationId = computed(() => route.query.stationId as string | undefined);
const stationName = computed(() => (meta.value.station?.name ?? route.query.stationName) as string | undefined);

const removedLicensesCount = computed(() => meta.value.removedLicenses?.length ?? 0);

const isPastLimit = computed(() => fetchTime.value < DateTime.now().setZone('UTC').minus({ hours: 24 }));

const selectedLineName = computed((): string => {
    const line = selectedTrain.value?.line;
    if (!line) return '';
    const name = line.name ?? line.fahrtNr ?? '';
    return name.replaceAll(/\(.*?\)/g, '').trim();
});

async function fetchDepartures(time?: string): Promise<void> {
    if (!stationId.value) return;
    if (time) fetchTime.value = DateTime.fromISO(time).setZone('UTC');

    if (isPastLimit.value) {
        notyf.error(trans('stationboard.no-past-data'));
        return;
    }

    loading.value = true;
    try {
        const when = fetchTime.value.toISO() ?? undefined;
        const res = await api.station.getDepartures(stationId.value, {
            when,
            travelType: travelType.value ? (travelType.value as TravelType) : undefined,
        });
        departures.value = res.data?.data ?? [];
        meta.value = (res.data?.meta as DepartureMeta) ?? {};
    } catch (e) {
        if (typeof e === 'object' && e !== null && 'status' in e && e.status === 502)
            notyf.error(trans('messages.exception.motis.502'));
        else notyf.error(trans('messages.exception.general'));
    } finally {
        loading.value = false;
    }
}

function fetchPrevious(): void {
    const prev = meta.value.times?.prev;
    fetchDepartures(prev ?? fetchTime.value.minus({ minutes: 15 }).toISO() ?? undefined);
}

function fetchNext(): void {
    const next = meta.value.times?.next;
    fetchDepartures(next ?? fetchTime.value.plus({ minutes: 15 }).toISO() ?? undefined);
}

function openModal(train: DepartureResource): void {
    selectedTrain.value = train;
    selectedDestination.value = null;
    modalRef.value?.showModal();
}

function closeModal(): void {
    selectedTrain.value = null;
    selectedDestination.value = null;
}

function handleBack(): void {
    if (selectedDestination.value) {
        selectedDestination.value = null;
    } else {
        modalRef.value?.close();
    }
}

function selectDestination(stopover: StopoverResource): void {
    selectedDestination.value = stopover;
}

function toLocalInput(value: DateTime): string {
    return value.setZone('local').toFormat("yyyy-MM-dd'T'HH:mm");
}

const fetchTimeLocal = ref<string>(toLocalInput(fetchTime.value));
let fetchTimeDebounce: ReturnType<typeof setTimeout> | null = null;

watch(fetchTime, (value) => {
    const formatted = toLocalInput(value);
    if (formatted !== fetchTimeLocal.value) fetchTimeLocal.value = formatted;
});

function onFetchTimeInput(): void {
    if (fetchTimeDebounce) clearTimeout(fetchTimeDebounce);
    fetchTimeDebounce = setTimeout(() => {
        const parsed = DateTime.fromISO(fetchTimeLocal.value, { zone: 'local' }).toUTC();
        if (parsed.isValid) fetchDepartures(parsed.toISO() ?? undefined);
    }, 500);
}

onBeforeUnmount(() => {
    if (fetchTimeDebounce) clearTimeout(fetchTimeDebounce);
});

const minDateTimeLocal = computed(() =>
    DateTime.now().setZone('local').minus({ hours: 24 }).toFormat("yyyy-MM-dd'T'HH:mm"),
);

function effectiveDepartureTime(item: DepartureResource): string {
    return item.when ?? item.plannedWhen ?? '';
}

function departureDate(item: DepartureResource): string {
    return DateTime.fromISO(effectiveDepartureTime(item)).toLocal().toISODate() ?? '';
}

function showDateSeparator(index: number): boolean {
    if (index === 0) {
        return departureDate(departures.value[0]) !== fetchTime.value.toLocal().toISODate();
    }
    return departureDate(departures.value[index]) !== departureDate(departures.value[index - 1]);
}

function getDateLabel(item: DepartureResource): string {
    return DateTime.fromISO(effectiveDepartureTime(item)).toLocal().toLocaleString(DateTime.DATE_HUGE);
}

function onTravelTypeChange(): void {
    fetchDepartures();
}

const travelTypeOptions = computed(() => [
    { value: '', label: trans('stationboard.filter-all') },
    { value: 'express', label: trans('transport_types.express') },
    { value: 'regional', label: trans('transport_types.regional') },
    { value: 'suburban', label: trans('transport_types.suburban') },
    { value: 'bus', label: trans('transport_types.bus') },
    { value: 'tram', label: trans('transport_types.tram') },
    { value: 'subway', label: trans('transport_types.subway') },
    { value: 'ferry', label: trans('transport_types.ferry') },
    { value: 'plane', label: trans('transport_types.plane') },
]);

onMounted(() => {
    if (route.query.when) {
        const parsed = DateTime.fromISO(route.query.when as string).setZone('UTC');
        if (parsed.isValid) fetchTime.value = parsed;
    }
    if (route.query.travelType) {
        travelType.value = route.query.travelType as string;
    }

    if (!stationId.value) {
        router.replace({ name: 'dashboard' });
        return;
    }

    fetchDepartures();
});

watch(router.currentRoute, (to) => {
    if (to.query.stationId !== stationId.value) return;
    if (to.query.when) {
        const parsed = DateTime.fromISO(to.query.when as string).setZone('UTC');
        if (parsed.isValid) fetchTime.value = parsed;
    }
    if (to.query.travelType) {
        travelType.value = to.query.travelType as string;
    }
    fetchDepartures();
});
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-1 min-w-0 mb-1">
                <h2 class="font-semibold text-lg truncate">
                    {{ stationName ?? '…' }}
                </h2>
                <StationTechnicalDetails v-if="meta.station" :station="meta.station" />
                <HomeStationToggle
                    v-if="userStore.user && meta.station?.uuid"
                    class="ms-auto"
                    :station-uuid="meta.station.uuid"
                    :station-name="meta.station.name"
                />
            </div>
            <StationSearch :small="true" />
            <div class="card bg-base-100 mb-2 md:mb-4">
                <div class="card-body py-3 px-4 gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <!-- Time navigation -->
                        <input
                            v-model="fetchTimeLocal"
                            type="datetime-local"
                            class="input input-bordered input-xs text-sm w-auto"
                            :min="minDateTimeLocal"
                            @input="onFetchTimeInput"
                        />
                        <!-- Travel type filter -->
                        <select
                            v-model="travelType"
                            class="select select-bordered select-xs max-w-[130px]"
                            @change="onTravelTypeChange"
                        >
                            <option v-for="opt in travelTypeOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Hidden licenses info -->
            <div
                v-if="removedLicensesCount > 0"
                class="collapse collapse-arrow bg-base-100 mb-2 md:mb-4 border border-info"
            >
                <input type="checkbox" />
                <div class="collapse-title text-sm flex items-center gap-2 text-info font-medium">
                    <TriangleAlert class="w-4 h-4 flex-shrink-0" />
                    {{ transChoice('stationboard.hidden-departures', removedLicensesCount) }}
                </div>
                <div class="collapse-content text-sm">
                    <p class="mb-2">{{ trans('stationboard.hidden-departures.detail') }}</p>
                    <p class="mb-2">{{ trans('stationboard.hidden-departures.detail2') }}</p>
                    <ul class="list-disc list-inside mb-2">
                        <li v-for="(license, i) in meta.removedLicenses" :key="i">
                            {{ license.licenseName ?? license }}
                        </li>
                    </ul>
                    <p>
                        {{ trans('stationboard.hidden-departures.detail3') }}
                        <a
                            :href="
                                getActiveLanguage().startsWith('de')
                                    ? 'https://help.traewelling.de/features/timetable/licensing/'
                                    : 'https://help.traewelling.de/en/features/timetable/licensing/'
                            "
                            target="_blank"
                            class="link link-info"
                        >
                            help.traewelling.de
                        </a>
                    </p>
                </div>
            </div>

            <!-- Top nav -->
            <div v-if="!loading" class="flex justify-center gap-3 mb-2 mb:md-4">
                <button class="btn btn-ghost btn-sm" :disabled="isPastLimit" @click="fetchPrevious">
                    <ChevronUp class="w-4 h-4" />
                    {{ trans('time.earlier') }}
                </button>
                <button class="btn btn-ghost btn-sm" @click="fetchNext">
                    {{ trans('time.later') }}
                    <ChevronDown class="w-4 h-4" />
                </button>
            </div>

            <!-- Loading skeleton -->
            <template v-if="loading">
                <div v-for="n in 8" :key="n" class="card bg-base-100 mb-1">
                    <div class="card-body py-2 px-3 flex flex-row items-center gap-3">
                        <div class="skeleton w-5 h-5 rounded-full flex-shrink-0" />
                        <div class="skeleton h-5 w-12 rounded flex-shrink-0" />
                        <div class="skeleton h-4 flex-1 rounded" />
                        <div class="skeleton h-4 w-10 rounded" />
                    </div>
                </div>
            </template>

            <template v-else>
                <!-- Empty state -->
                <div v-if="!departures.length" class="card bg-base-100">
                    <div class="card-body items-center text-center gap-2 py-10">
                        <p class="text-base-content/60 text-sm">{{ trans('stationboard.no-departures') }}</p>
                    </div>
                </div>

                <!-- Departure list -->
                <template v-for="(item, index) in departures" :key="item.tripId + item.plannedWhen">
                    <div
                        v-if="showDateSeparator(index)"
                        class="flex items-center gap-3 my-2"
                        :class="index > 0 ? 'mt-4' : ''"
                    >
                        <div class="flex-1 h-px bg-base-300" />
                        <span class="text-xs font-medium text-base-content/50 shrink-0">
                            {{ getDateLabel(item) }}
                        </span>
                        <div class="flex-1 h-px bg-base-300" />
                    </div>
                    <DepartureEntry :item="item" :station-name="stationName" @click="openModal(item)" />
                </template>
            </template>

            <!-- Next / Prev bottom nav -->
            <div v-if="!loading && departures.length" class="flex justify-center gap-3 mt-3">
                <button class="btn btn-ghost btn-sm" :disabled="isPastLimit" @click="fetchPrevious">
                    <ChevronUp class="w-4 h-4" />
                    {{ trans('time.earlier') }}
                </button>
                <button class="btn btn-ghost btn-sm" @click="fetchNext">
                    {{ trans('time.later') }}
                    <ChevronDown class="w-4 h-4" />
                </button>
            </div>

            <!-- Manual trip creation -->
            <div v-if="!loading && userStore.user" class="flex flex-col items-center gap-2 mt-4">
                <p class="text-sm text-base-content/50">{{ trans('missing-journey') }}</p>
                <router-link :to="{ name: 'trip-create' }" class="btn btn-outline btn-sm gap-2">
                    <Plus class="w-4 h-4" />
                    {{ trans('create-journey') }}
                </router-link>
            </div>
        </div>

        <!-- Check-in modal -->
        <dialog ref="modalRef" class="modal" @close="closeModal">
            <div
                class="modal-box p-0 max-w-2xl w-full flex flex-col h-full max-h-screen sm:max-h-[90vh] rounded-none sm:rounded-box"
            >
                <!-- Modal header -->
                <div class="flex items-center gap-3 px-4 py-3 border-b border-base-200 flex-shrink-0">
                    <button class="btn btn-ghost btn-sm btn-square" @click="handleBack">
                        <ArrowLeft class="w-4 h-4" />
                    </button>

                    <template v-if="selectedTrain">
                        <div class="w-5 h-5 flex items-center justify-center text-base-content/60 flex-shrink-0">
                            <TransportIcon :product="selectedTrain.line?.product" :mode="selectedTrain.line?.mode" />
                        </div>
                        <LineIndicator
                            :mode="selectedTrain.line?.mode ?? null"
                            :product-name="selectedTrain.line?.product ?? ''"
                            :number="selectedLineName"
                            :background-color="selectedTrain.line?.color ?? null"
                            :color="selectedTrain.line?.textColor ?? null"
                        />
                        <span class="font-medium text-sm truncate">{{ selectedTrain.direction }}</span>
                        <template v-if="selectedDestination">
                            <span class="text-base-content/40 text-sm flex-shrink-0">→</span>
                            <span class="text-sm truncate text-base-content/70 flex-shrink-0">
                                {{ selectedDestination.name }}
                            </span>
                        </template>
                    </template>
                </div>

                <!-- Modal body -->
                <div class="overflow-y-auto flex-1">
                    <LineRun
                        v-if="selectedTrain && !selectedDestination"
                        :trip-id="selectedTrain.tripId"
                        :line-name="selectedTrain.line?.name ?? selectedTrain.line?.fahrtNr"
                        :start-id="selectedTrain.stop?.id ?? 0"
                        :planned-when="selectedTrain.plannedWhen"
                        @select="selectDestination"
                    />
                    <CheckinForm
                        v-else-if="selectedTrain && selectedDestination"
                        :key="selectedDestination.id"
                        :departure="selectedTrain"
                        :destination="selectedDestination"
                    />
                </div>
            </div>

            <!-- Backdrop closes modal -->
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    </AppLayout>
</template>
