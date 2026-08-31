<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { computed, inject, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import {
    Api,
    CheckinRequestBody,
    CheckinSuccessResource,
    DepartureResource,
    EventResource,
    StatusResource,
    StopoverResource,
} from '../../../types/Api.gen';
import { useActiveCheckin } from '../../../vue/stores/activeCheckin';
import { checkinSuccessStore } from '../../../vue/stores/checkinSuccess';
import { useProfileSettingsStore } from '../../../vue/stores/profileSettings';
import { getVisibilityOptions } from '../../helpers/visibility';
import EventPicker from './EventPicker.vue';
import FriendPicker from './FriendPicker.vue';
import TagEditor from './TagEditor.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });

const props = defineProps<{
    departure: DepartureResource;
    destination: StopoverResource;
    tripUuid?: string | null;
}>();

const router = useRouter();
const notyf = inject('notyf') as Notyf;
const activeCheckin = useActiveCheckin();
const checkinSuccess = checkinSuccessStore();
const profileStore = useProfileSettingsStore();

const statusText = ref('');
const visibility = ref(0);
const business = ref(0);
const toot = ref(false);
const chainPost = ref(false);
const loading = ref(false);
const collision = ref(false);
const collisionCheckins = ref<StatusResource[]>([]);
const selectedEvent = ref<EventResource | null>(null);
const selectedFriendIds = ref<number[]>([]);
const tagEditor = ref<InstanceType<typeof TagEditor> | null>(null);

const remaining = computed(() => 280 - statusText.value.length);

const visibilityOptions = computed(getVisibilityOptions);

const businessOptions = computed(() => [
    { value: 0, label: trans('stationboard.business.private') },
    { value: 1, label: trans('stationboard.business.business') },
    { value: 2, label: trans('stationboard.business.commute') },
]);

const departureTimestamp = computed(() =>
    props.departure.plannedWhen
        ? (DateTime.fromISO(props.departure.plannedWhen).setZone('UTC').toISO() ?? undefined)
        : undefined,
);

onMounted(() => {
    profileStore.fetchSettings();
});

watch(
    () => profileStore.getDefaultStatusVisibility,
    (v) => {
        visibility.value = v;
    },
    { immediate: true },
);

async function checkIn(): Promise<void> {
    loading.value = true;
    const arrival = props.destination.arrivalPlanned ?? props.destination.departurePlanned;

    const data: CheckinRequestBody = {
        body: statusText.value || undefined,
        toot: toot.value || undefined,
        chainPost: toot.value && chainPost.value ? true : undefined,
        visibility: visibility.value,
        business: business.value,
        tripId: props.departure.tripId,
        lineName: props.departure.line?.name ?? props.departure.line?.fahrtNr,
        start: props.departure.stop?.id,
        destination: props.destination.id,
        departure: DateTime.fromISO(props.departure.plannedWhen).setZone('UTC').toISO() ?? undefined,
        arrival: arrival ? (DateTime.fromISO(arrival).setZone('UTC').toISO() ?? undefined) : undefined,
        force: collision.value || undefined,
        eventId: selectedEvent.value?.id ?? undefined,
        with: selectedFriendIds.value.length ? selectedFriendIds.value : undefined,
    };

    try {
        const res = await api.trains.createCheckin(data);
        const json: CheckinSuccessResource = res.data;
        activeCheckin.reset();
        checkinSuccess.setResponse(json.data);
        await tagEditor.value?.postTags(json.data.status.id);
        router.push({ name: 'single-status', params: { id: json.data.status.id } });
    } catch (e) {
        const status = typeof e === 'object' && e !== null && 'status' in e ? (e as { status: number }).status : 0;
        if (status === 409) {
            const err = e as { error?: { data?: { conflicts?: StatusResource[] } } };
            collision.value = true;
            collisionCheckins.value = err.error?.data?.conflicts ?? [];
        } else {
            const msg =
                typeof e === 'object' && e !== null && 'error' in e
                    ? (e as { error: { message?: string } }).error?.message
                    : undefined;
            notyf.error(msg ?? trans('messages.exception.general'));
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex flex-col gap-4 p-4">
        <!-- Collision warning -->
        <div v-if="collision" class="alert alert-warning flex flex-col gap-2 text-sm" role="alert">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0" />
                <div class="flex flex-col gap-1">
                    <p class="font-semibold">{{ trans('checkin.conflict') }}</p>
                    <ul v-if="collisionCheckins.length" class="flex flex-col gap-1">
                        <li v-for="conflict in collisionCheckins" :key="conflict.id">
                            <a
                                :href="'/status/' + conflict.id"
                                target="_blank"
                                rel="noopener"
                                class="link link-hover flex flex-wrap items-baseline gap-x-1"
                            >
                                <strong>{{ conflict.checkin?.lineName }}</strong>
                                <span v-if="conflict.checkin?.origin?.departurePlanned" class="opacity-70">
                                    {{ DateTime.fromISO(conflict.checkin.origin.departurePlanned).toFormat('HH:mm') }}
                                </span>
                                <span v-if="conflict.checkin?.origin?.name">{{ conflict.checkin.origin.name }}</span>
                                <span v-if="conflict.checkin?.destination?.name">
                                    &rarr; {{ conflict.checkin.destination.name }}
                                </span>
                                <span v-if="conflict.checkin?.destination?.arrivalPlanned" class="opacity-70">
                                    {{
                                        DateTime.fromISO(conflict.checkin.destination.arrivalPlanned).toFormat('HH:mm')
                                    }}
                                </span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs opacity-50" />
                            </a>
                        </li>
                    </ul>
                    <p>{{ trans('checkin.conflict.question') }}</p>
                </div>
            </div>
        </div>

        <!-- Status text -->
        <div class="form-control">
            <textarea
                v-model="statusText"
                class="textarea textarea-bordered w-full resize-none"
                rows="4"
                :placeholder="trans('stationboard.label-message')"
                maxlength="280"
            />
            <div class="label justify-end">
                <span
                    class="label-text-alt"
                    :class="remaining < 20 ? (remaining < 10 ? 'text-error' : 'text-warning') : 'text-base-content/40'"
                >
                    {{ remaining }}
                </span>
            </div>
        </div>

        <!-- Selects row: visibility + business -->
        <div class="flex flex-wrap gap-2">
            <select v-model="visibility" class="select select-bordered select-sm flex-1 min-w-[130px]">
                <option v-for="opt in visibilityOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                </option>
            </select>

            <select v-model="business" class="select select-bordered select-sm flex-1 min-w-[110px]">
                <option v-for="opt in businessOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                </option>
            </select>
        </div>

        <!-- Mastodon toggles -->
        <div v-if="profileStore.getMastodon" class="flex flex-wrap gap-4 items-center">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input v-model="toot" type="checkbox" class="checkbox checkbox-sm" :disabled="visibility === 3" />
                <span>{{ trans('stationboard.check-toot') }}</span>
            </label>
            <label v-if="toot" class="flex items-center gap-2 text-sm cursor-pointer">
                <input v-model="chainPost" type="checkbox" class="checkbox checkbox-sm" :disabled="visibility === 3" />
                <span>{{ trans('stationboard.check-chainPost') }}</span>
            </label>
        </div>

        <!-- Extra options: event, friends -->
        <div class="flex flex-wrap gap-2 items-start">
            <EventPicker v-model="selectedEvent" :timestamp="departureTimestamp" />
            <FriendPicker v-model="selectedFriendIds" />
        </div>

        <!-- Tags -->
        <TagEditor ref="tagEditor" :trip-uuid="props.tripUuid" />

        <!-- Submit -->
        <button
            class="btn w-full"
            :class="collision ? 'btn-warning' : 'btn-primary'"
            :disabled="loading"
            @click="checkIn"
        >
            <span v-if="loading" class="loading loading-spinner loading-sm" />
            {{ collision ? trans('checkin.conflict.force') : trans('stationboard.btn-checkin') }}
        </button>
    </div>
</template>
