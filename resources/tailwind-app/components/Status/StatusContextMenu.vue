<script setup lang="ts">
import {
    CopyPlus,
    Eye,
    MoreVertical,
    PlaneLanding,
    PlaneTakeoff,
    Share2,
    ShieldCogCorner,
    Trash2,
    UserPlus,
    UserX,
    VolumeX,
    ArrowBigRightDash,
} from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime as LuxonDateTime } from 'luxon';
import { Notyf } from 'notyf';
import { computed, inject, ref } from 'vue';
import { useRouter } from 'vue-router';
import { Api, StatusResource, TripResource } from '../../../types/Api.gen';
import { getArrivalAttribute } from '../../../vue/helpers/DateTimeHelper';
import { useUserStore } from '../../../vue/stores/user';
import TripCopyModal from '../Trip/TripCopyModal.vue';

const props = defineProps<{
    status: StatusResource;
}>();

const emit = defineEmits<{
    edit: [];
    delete: [];
    'status-updated': [status: StatusResource];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;
const userStore = useUserStore();
const router = useRouter();

const busyDepartureNow = ref(false);
const busyArrivalNow = ref(false);
const busyMute = ref(false);
const busyBlock = ref(false);
const copyTripModalOpen = ref(false);

const isOwn = computed(() => !!userStore.user && userStore.user.id === props.status.user.id);

const canCopyTrip = computed(() => isOwn.value && !!props.status.checkin.tripUuid);

const connectionsLink = computed(() => {
    const dest = props.status.checkin.destination;
    const arrival = getArrivalAttribute(props.status);
    return {
        name: 'stationboard',
        query: {
            stationId: dest.id,
            stationName: dest.name,
            when: arrival.time?.toISO() ?? undefined,
        },
    };
});

const showDepartureNowBtn = computed(() => {
    if (!isOwn.value) return false;
    const origin = props.status.checkin.origin;
    const dest = props.status.checkin.destination;
    const dep = LuxonDateTime.fromISO(origin.departurePlanned ?? origin.departureReal ?? '');
    const arr = LuxonDateTime.fromISO(dest.arrivalPlanned ?? dest.arrivalReal ?? '');
    const now = LuxonDateTime.now();
    return dep.isValid && arr.isValid && now >= dep.minus({ minutes: 60 }) && now <= arr.plus({ days: 1 });
});

const showArrivalNowBtn = computed(() => {
    if (!isOwn.value) return false;
    const origin = props.status.checkin.origin;
    const dest = props.status.checkin.destination;
    const dep = LuxonDateTime.fromISO(origin.departurePlanned ?? origin.departureReal ?? '');
    const arr = LuxonDateTime.fromISO(dest.arrivalPlanned ?? dest.arrivalReal ?? '');
    const now = LuxonDateTime.now();
    return dep.isValid && arr.isValid && now >= dep && now <= arr.plus({ days: 1 });
});

function getNowIso(): string {
    return LuxonDateTime.now()
        .set({ second: 0, millisecond: 0 })
        .toISO({ suppressSeconds: true, suppressMilliseconds: true });
}

function share() {
    const url = `${window.location.origin}/status/${props.status.id}`;
    const origin = props.status.checkin.origin.name;
    const dest = props.status.checkin.destination.name;
    const text = props.status.body
        ? `${props.status.body} (@ ${props.status.checkin.lineName} ${origin} -> ${dest}) #NowTräwelling`
        : `${props.status.checkin.lineName} ${origin} -> ${dest} #NowTräwelling`;

    if (navigator.share) {
        navigator.share({ title: 'Träwelling', text, url }).catch(() => {});
    } else {
        navigator.clipboard.writeText(`${text} ${url}`).then(() => {
            notyf?.success(trans('menu.share.clipboard.success'));
        });
    }
}

function rideAlongRoute() {
    const t = props.status.checkin;
    return {
        name: 'checkin',
        query: {
            tripId: t.trip.toString(),
            lineName: t.lineName,
            start: t.origin.id.toString(),
            destination: t.destination.id.toString(),
            departure: t.origin.departurePlanned ?? t.origin.departureReal ?? '',
            originName: t.origin.name,
            destinationName: t.destination.name,
            category: t.category,
        },
    };
}

async function departureNow() {
    busyDepartureNow.value = true;
    try {
        const res = await api.status.updateSingleStatus({ manualDeparture: getNowIso() } as never, props.status.id);
        emit('status-updated', res.data.data as StatusResource);
    } finally {
        busyDepartureNow.value = false;
    }
}

async function arrivalNow() {
    busyArrivalNow.value = true;
    try {
        const res = await api.status.updateSingleStatus({ manualArrival: getNowIso() } as never, props.status.id);
        emit('status-updated', res.data.data as StatusResource);
    } finally {
        busyArrivalNow.value = false;
    }
}

async function onTripCopied(copy: TripResource) {
    copyTripModalOpen.value = false;
    notyf?.success(trans('trip.copy.success'));
    await router.push({ name: 'trip-edit', params: { uuid: copy.uuid } });
}

async function handleMute() {
    busyMute.value = true;
    try {
        await api.user.createMute(props.status.user.id as unknown as number);
        notyf?.success(trans('user.muted', { username: props.status.user.username }));
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        busyMute.value = false;
    }
}

async function handleBlock() {
    busyBlock.value = true;
    try {
        await api.user.createBlock(props.status.user.id.toString());
        notyf?.success(trans('user.blocked', { username: props.status.user.username }));
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        busyBlock.value = false;
    }
}
</script>

<template>
    <div class="dropdown dropdown-end">
        <button tabindex="0" class="btn btn-ghost btn-xs btn-circle text-base-content/40">
            <MoreVertical class="inline-block size-4" />
        </button>
        <ul
            tabindex="0"
            class="dropdown-content menu bg-base-100 rounded-box z-20 w-48 p-1 shadow-lg border border-base-200"
        >
            <li>
                <button @click="share">
                    <Share2 class="inline-block size-4" />
                    {{ trans('menu.share') }}
                </button>
            </li>

            <template v-if="userStore.user">
                <template v-if="isOwn">
                    <li v-if="showDepartureNowBtn">
                        <button :disabled="busyDepartureNow" @click="departureNow">
                            <PlaneTakeoff class="inline-block size-4" />
                            {{ trans('status.departure-now') }}
                        </button>
                    </li>
                    <li v-if="showArrivalNowBtn">
                        <button :disabled="busyArrivalNow" @click="arrivalNow">
                            <PlaneLanding class="inline-block size-4" />
                            {{ trans('status.arrival-now') }}
                        </button>
                    </li>
                    <li>
                        <button :to="connectionsLink">
                            <ArrowBigRightDash class="inline-block size-4" />
                            {{ trans('status.further-connections') }}
                        </button>
                    </li>
                    <li>
                        <button @click="emit('edit')">
                            <Eye class="inline-block size-4" />
                            {{ trans('edit') }}
                        </button>
                    </li>
                    <li v-if="canCopyTrip">
                        <button @click="copyTripModalOpen = true">
                            <CopyPlus class="inline-block size-4" />
                            {{ trans('trip.copy.title') }}
                        </button>
                    </li>
                    <li>
                        <button class="text-error" @click="emit('delete')">
                            <Trash2 class="inline-block size-4" />
                            {{ trans('delete') }}
                        </button>
                    </li>
                </template>
                <template v-else>
                    <li>
                        <router-link :to="rideAlongRoute()">
                            <UserPlus class="inline-block size-4" />
                            {{ trans('status.join') }}
                        </router-link>
                    </li>
                    <li>
                        <button :disabled="busyMute" @click="handleMute">
                            <VolumeX class="inline-block size-4" />
                            {{ trans('user.mute-tooltip') }}
                        </button>
                    </li>
                    <li>
                        <button class="text-error" :disabled="busyBlock" @click="handleBlock">
                            <UserX class="inline-block size-4" />
                            {{ trans('user.block-tooltip') }}
                        </button>
                    </li>
                </template>
                <template v-if="userStore.isAdmin">
                    <li class="menu-title text-xs">Admin</li>
                    <li>
                        <a :href="`/admin/statuses/${status.id}`">
                            <ShieldCogCorner class="inline-block size-4" />
                            {{ trans('menu.backend') }}
                        </a>
                    </li>
                </template>
            </template>
        </ul>

        <TripCopyModal
            v-if="canCopyTrip"
            :open="copyTripModalOpen"
            :trip-uuid="status.checkin.tripUuid!"
            :points="status.checkin.points"
            @close="copyTripModalOpen = false"
            @copied="onTripCopied"
        />
    </div>
</template>
