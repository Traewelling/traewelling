<script setup lang="ts">
import { Briefcase, Building2, Calendar, User } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime as LuxonDateTime } from 'luxon';
import { Notyf } from 'notyf';
import { inject, ref, watch } from 'vue';
import {
    Api,
    Business,
    EventResource,
    StatusResource,
    StatusUpdateBody,
    StatusVisibility,
    StopoverResource,
} from '../../../types/Api.gen';
import { getDepartureForStatus } from '../../../vue/helpers/DateTimeHelper';
import { ALL_VISIBILITIES, VISIBILITY_ICONS } from '../../helpers/visibility';

const props = defineProps<{
    open: boolean;
    status: StatusResource;
}>();

const emit = defineEmits<{
    close: [];
    saved: [status: StatusResource];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;

const loading = ref(false);
const stopovers = ref<StopoverResource[]>([]);
const destinationValue = ref<string | null>(null);
const manualDeparture = ref('');
const manualArrival = ref('');
const body = ref('');
const business = ref<Business>(Business.Value0);
const visibility = ref<StatusVisibility>(StatusVisibility.Value0);
const eventId = ref<number | null>(null);
const events = ref<EventResource[]>([]);

function isoToDatetimeLocal(iso: string | null | undefined): string {
    if (!iso) return '';
    return LuxonDateTime.fromISO(iso).toFormat("yyyy-MM-dd'T'HH:mm");
}

function closeDropdown() {
    (document.activeElement as HTMLElement)?.blur();
}

async function loadData() {
    body.value = props.status.body ?? '';
    business.value = props.status.business ?? Business.Value0;
    visibility.value = props.status.visibility ?? StatusVisibility.Value0;
    eventId.value = props.status.event?.id ?? null;
    manualDeparture.value = isoToDatetimeLocal(props.status.checkin.manualDeparture);
    manualArrival.value = isoToDatetimeLocal(props.status.checkin.manualArrival);
    destinationValue.value = null;
    stopovers.value = [];

    try {
        const res = await api.trains.getTrainTrip({
            hafasTripId: props.status.checkin.trip,
            lineName: props.status.checkin.lineName,
        });
        const all: StopoverResource[] = res.data?.data?.stopovers ?? [];
        const depPlanned = LuxonDateTime.fromISO(props.status.checkin.origin.departurePlanned ?? '');
        stopovers.value = all.filter((s) => {
            const arr = LuxonDateTime.fromISO(
                s.arrivalPlanned ?? s.arrivalReal ?? s.departurePlanned ?? s.departureReal ?? '',
            );
            return arr.isValid && depPlanned.isValid && arr.diff(depPlanned).as('minutes') >= 0;
        });
        const cur = stopovers.value.find(
            (s) =>
                s.id === props.status.checkin.destination.id &&
                s.arrivalPlanned === props.status.checkin.destination.arrivalPlanned,
        );
        if (cur?.arrivalPlanned) {
            destinationValue.value = `${cur.id}|${cur.arrivalPlanned}`;
        }
    } catch {
        // stopovers best-effort
    }

    try {
        const dep = getDepartureForStatus(props.status).toISO() ?? '';
        const evRes = await api.events.getEvents({ timestamp: dep });
        events.value = evRes.data.data ?? [];
    } catch {
        events.value = [];
    }
}

watch(
    () => props.open,
    (open) => {
        if (open) loadData();
    },
    { immediate: true },
);

async function save() {
    loading.value = true;
    try {
        const updateBody: StatusUpdateBody = {
            body: body.value || null,
            business: business.value,
            visibility: visibility.value,
            eventId: eventId.value,
            manualDeparture: manualDeparture.value ? LuxonDateTime.fromISO(manualDeparture.value).toISO() : null,
            manualArrival: manualArrival.value ? LuxonDateTime.fromISO(manualArrival.value).toISO() : null,
        } as StatusUpdateBody;

        if (destinationValue.value) {
            const idx = destinationValue.value.indexOf('|');
            updateBody.destinationId = Number(destinationValue.value.slice(0, idx)).toString();
            updateBody.destinationArrivalPlanned = destinationValue.value.slice(idx + 1);
        }

        const res = await api.status.updateSingleStatus(updateBody, props.status.id);
        emit('saved', res.data.data as StatusResource);
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <dialog class="modal" :class="{ 'modal-open': open }">
        <div class="modal-box w-11/12 max-w-xl overflow-visible">
            <h3 class="font-bold text-lg mb-4">{{ trans('modals.editStatus-title') }}</h3>

            <!-- Destination -->
            <div class="form-control mb-3">
                <label class="label"
                    ><span class="label-text">{{ trans('exit') }}</span></label
                >
                <select v-model="destinationValue" class="select select-bordered w-full">
                    <option
                        v-for="s in stopovers"
                        :key="`${s.id}-${s.arrivalPlanned}`"
                        :value="`${s.id}|${s.arrivalPlanned}`"
                    >
                        {{ LuxonDateTime.fromISO(s.arrivalPlanned ?? s.arrivalReal ?? '').toFormat('HH:mm') }}:
                        {{ s.name }}
                    </option>
                </select>
            </div>

            <!-- Manual times -->
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text text-xs">{{ trans('export.title.departure_real') }}</span>
                    </label>
                    <input v-model="manualDeparture" type="datetime-local" class="input input-bordered input-sm" />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text text-xs">{{ trans('export.title.arrival_real') }}</span>
                    </label>
                    <input v-model="manualArrival" type="datetime-local" class="input input-bordered input-sm" />
                </div>
            </div>

            <!-- Body -->
            <div class="form-control mb-3">
                <textarea
                    v-model="body"
                    class="textarea textarea-bordered w-full"
                    :placeholder="trans('modals.editStatus-label')"
                    maxlength="280"
                    rows="4"
                />
                <div v-if="(body || '').length > 100" class="label">
                    <span class="label-text-alt text-base-content/50">{{ (body || '').length }}/280</span>
                </div>
            </div>

            <!-- Business, Visibility, Event -->
            <div class="flex flex-wrap gap-2 mb-4">
                <!-- Business dropdown -->
                <div class="dropdown">
                    <button tabindex="0" class="btn btn-sm btn-outline gap-1">
                        <User v-if="business === Business.Value0" class="inline-block size-4" />
                        <Briefcase v-else-if="business === Business.Value1" class="inline-block size-4" />
                        <Building2 v-else class="inline-block size-4" />
                        {{
                            business === Business.Value0
                                ? trans('stationboard.business.private')
                                : business === Business.Value1
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
                                business = Business.Value0;
                                closeDropdown();
                            "
                        >
                            <a :class="business === Business.Value0 ? 'active' : ''">
                                <User class="w-4 h-4 shrink-0" />
                                <span>{{ trans('stationboard.business.private') }}</span>
                            </a>
                        </li>
                        <li
                            @click="
                                business = Business.Value1;
                                closeDropdown();
                            "
                        >
                            <a :class="business === Business.Value1 ? 'active' : ''">
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
                                business = Business.Value2;
                                closeDropdown();
                            "
                        >
                            <a :class="business === Business.Value2 ? 'active' : ''">
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
                <div v-if="!status.lock_visibility" class="dropdown">
                    <button tabindex="0" class="btn btn-sm btn-outline gap-1">
                        <component :is="VISIBILITY_ICONS[visibility]" class="inline-block size-4" />
                        {{ trans('status.visibility.' + visibility) }}
                    </button>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-base-100 rounded-box z-10 w-72 p-2 shadow-lg border border-base-200"
                    >
                        <li
                            v-for="v in ALL_VISIBILITIES"
                            :key="v"
                            @click="
                                visibility = v as StatusVisibility;
                                closeDropdown();
                            "
                        >
                            <a :class="visibility === v ? 'active' : ''">
                                <component :is="VISIBILITY_ICONS[v]" class="w-4 h-4 shrink-0" />
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
                        <Calendar class="inline-block size-4" />
                        {{
                            eventId
                                ? (events.find((e) => e.id === eventId)?.name ?? '…')
                                : trans('events.no-event-dropdown')
                        }}
                    </button>
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-base-100 rounded-box z-10 w-72 p-2 shadow-lg border border-base-200 max-h-60 overflow-y-auto"
                    >
                        <li
                            @click="
                                eventId = null;
                                closeDropdown();
                            "
                        >
                            <a :class="eventId === null ? 'active' : ''">
                                <Calendar class="w-4 h-4 shrink-0" />
                                {{ trans('events.no-event-dropdown') }}
                            </a>
                        </li>
                        <li
                            v-for="e in events"
                            :key="e.id"
                            @click="
                                eventId = e.id;
                                closeDropdown();
                            "
                        >
                            <a :class="eventId === e.id ? 'active' : ''">
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
                <button class="btn btn-ghost" @click="emit('close')">{{ trans('cancel') }}</button>
                <button class="btn btn-primary" :disabled="loading" @click="save">
                    <span v-if="loading" class="loading loading-spinner loading-xs" />
                    {{ trans('modals.edit-confirm') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @submit.prevent="emit('close')">
            <button>close</button>
        </form>
    </dialog>
</template>
