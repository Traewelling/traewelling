<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { computed, PropType, ref, useTemplateRef } from 'vue';
import { Api, StatusResource, StatusUpdateBody } from '../../../../types/Api.gen';
import { StatusHelper } from '../../../helpers/StatusHelper';
import { useUserStore } from '../../../stores/user';
import ConfirmModal from '../../ConfirmModal.vue';
import UpdateModal from '../../UpdateModal/UpdateModal.vue';

const props = defineProps({
    status: {
        type: Object as PropType<StatusResource>,
        required: true,
    },
});

const emit = defineEmits(['confirm-delete', 'status-deleted', 'status-updated']);
const notyf = new Notyf({ position: { x: 'right', y: 'bottom' } });
const user = useUserStore();

function share() {
    let helper = new StatusHelper(props.status);
    let shareText =
        props.status?.userDetails.id === user.user?.id ? helper.generateSocialText() : helper.getDescription();
    let shareUrl = helper.getShareUrl();

    if (navigator.share) {
        navigator
            .share({
                title: 'Träwelling',
                text: shareText,
                url: shareUrl,
            })
            .catch((error) => {
                console.error('Error sharing:', error);
            });
    } else {
        navigator.clipboard.writeText(shareText + ' ' + shareUrl).then(() => {
            notyf.success('Copied to clipboard');
        });
    }
}

function rideAlongUrl() {
    let queryParams = new URLSearchParams({
        tripId: props.status?.train.trip.toString(),
        lineName: props.status?.train.lineName,
        start: props.status?.train.origin.id.toString(),
        destination: props.status?.train.destination.id.toString(),
        departure: props.status?.train.origin.departurePlanned
            ? props.status?.train.origin.departurePlanned.toString()
            : '',
        idType: 'trwl',
        category: props.status?.train.category,
    });

    return `/stationboard/?${queryParams.toString()}`;
}

const delModal = useTemplateRef('delModal');
const updateModal = useTemplateRef('updateModal');

function showModal() {
    delModal.value?.show();
}

const showDepartureNowButton = computed(() => {
    const train = props.status?.train;
    if (!train || !train.origin || !train.destination) return false;

    const plannedDeparture = DateTime.fromISO(train.origin.departurePlanned || train.origin.departure || '');
    const plannedArrival = DateTime.fromISO(train.destination.arrivalPlanned || train.destination.arrival || '');
    if (!plannedDeparture.isValid || !plannedArrival.isValid) return false;

    const now = DateTime.now();
    return now >= plannedDeparture.minus({ minutes: 60 }) && now <= plannedArrival.plus({ days: 1 });
});

const showArrivalNowButton = computed(() => {
    const train = props.status?.train;
    if (!train || !train.origin || !train.destination) return false;

    const plannedDeparture = DateTime.fromISO(train.origin.departurePlanned || train.origin.departure || '');
    const plannedArrival = DateTime.fromISO(train.destination.arrivalPlanned || train.destination.arrival || '');
    if (!plannedDeparture.isValid || !plannedArrival.isValid) return false;

    const now = DateTime.now();
    return now >= plannedDeparture && now <= plannedArrival.plus({ days: 1 });
});

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

function getNowWithoutSeconds(): string {
    return DateTime.now()
        .set({ second: 0, millisecond: 0 })
        .toISO({ suppressSeconds: true, suppressMilliseconds: true });
}

function departureNow() {
    api.status
        .updateSingleStatus({ manualDeparture: getNowWithoutSeconds() } as StatusUpdateBody, props.status.id)
        .then((status) => {
            emit('status-updated', status.data.data);
        })
        .catch((error) => {
            console.error('Error updating status:', error);
        });
}

function arrivalNow() {
    api.status
        .updateSingleStatus({ manualArrival: getNowWithoutSeconds() } as StatusUpdateBody, props.status.id)
        .then((status) => {
            emit('status-updated', status.data.data);
        })
        .catch((error) => {
            console.error('Error updating status:', error);
        });
}

const canModerateTarget = computed(() => !!user.user && user.user.id !== props.status.userDetails.id);

const busyMute = ref(false);
const busyBlock = ref(false);

async function handleMute() {
    if (!canModerateTarget.value) return;
    busyMute.value = true;
    try {
        await api.user.createMute(props.status.userDetails.id as unknown as number);
        notyf.success(trans('user.muted', { username: props.status.userDetails.username }));
    } catch (e) {
        console.error('Mute failed:', e);
        notyf.error(trans('generic.error'));
    } finally {
        busyMute.value = false;
    }
}

async function handleBlock() {
    if (!canModerateTarget.value) return;
    busyBlock.value = true;
    try {
        const targetId = props.status.userDetails.id;
        await api.user.createBlock(String(targetId), { userId: targetId });
        notyf.success(trans('user.blocked', { username: props.status.userDetails.username }));
    } catch (e) {
        console.error('Block failed:', e);
        notyf.error(trans('generic.error'));
    } finally {
        busyBlock.value = false;
    }
}
</script>

<template>
    <div class="dropdown dropdown-flex">
        <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
            &nbsp;
            <i class="fa fa-ellipsis-vertical" aria-hidden="true" />
            &nbsp;
        </a>
        <ul class="dropdown-menu">
            <li>
                <button class="dropdown-item" type="button" @click="share">
                    <div class="dropdown-icon-suspense">
                        <i class="fas fa-share" aria-hidden="true" />
                    </div>
                    {{ trans('menu.share') }}
                </button>
            </li>
            <template v-if="user.user">
                <template v-if="user.user.id == status.userDetails.id">
                    <template v-if="showArrivalNowButton || showDepartureNowButton">
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li v-if="showDepartureNowButton">
                            <button class="dropdown-item" type="button" @click="departureNow()">
                                <div class="dropdown-icon-suspense">
                                    <i class="fa-solid fa-plane-departure" aria-hidden="true" />
                                </div>
                                {{ trans('status.departure-now') }}
                            </button>
                        </li>
                        <li v-if="showArrivalNowButton">
                            <button class="dropdown-item" type="button" @click="arrivalNow()">
                                <div class="dropdown-icon-suspense">
                                    <i class="fa-solid fa-plane-arrival" aria-hidden="true" />
                                </div>
                                {{ trans('status.arrival-now') }}
                            </button>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                    </template>
                    <li>
                        <button class="dropdown-item" type="button" @click="updateModal?.show()">
                            <div class="dropdown-icon-suspense">
                                <i class="fas fa-edit" aria-hidden="true" />
                            </div>
                            {{ trans('edit') }}
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item" type="button" @click="showModal()">
                            <div class="dropdown-icon-suspense">
                                <i class="fas fa-trash" aria-hidden="true" />
                            </div>
                            {{ trans('delete') }}
                        </button>
                    </li>
                </template>
                <template v-else>
                    <li>
                        <a :href="rideAlongUrl()" class="dropdown-item">
                            <div class="dropdown-icon-suspense">
                                <i class="fas fa-user-plus" aria-hidden="true" />
                            </div>
                            {{ trans('status.join') }}
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <a :href="`/report?subjectType=Status&subjectId=${status.id}`" class="dropdown-item">
                            <div class="dropdown-icon-suspense">
                                <i class="fas fa-flag" aria-hidden="true" />
                            </div>
                            {{ trans('status.report') }}
                        </a>
                    </li>

                    <li v-if="canModerateTarget">
                        <button class="dropdown-item" type="button" :disabled="busyMute" @click="handleMute">
                            <div class="dropdown-icon-suspense">
                                <i class="fas fa-volume-mute" aria-hidden="true" />
                            </div>
                            {{ trans('user.mute-tooltip') }}
                        </button>
                    </li>

                    <li v-if="canModerateTarget">
                        <button
                            class="dropdown-item text-danger"
                            type="button"
                            :disabled="busyBlock"
                            @click="handleBlock"
                        >
                            <div class="dropdown-icon-suspense">
                                <i class="fas fa-ban" aria-hidden="true" />
                            </div>
                            {{ trans('user.block-tooltip') }}
                        </button>
                    </li>
                </template>
                <template v-if="user?.isAdmin">
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <a :href="`/admin/statuses/${status.id}/edit`" class="dropdown-item">
                            <div class="dropdown-icon-suspense">
                                <i class="fas fa-tools" aria-hidden="true" />
                            </div>
                            Admin-Interface
                        </a>
                    </li>
                </template>
            </template>
        </ul>
    </div>
    <ConfirmModal ref="delModal" title="modals.deleteStatus-title" @confirm="emit('confirm-delete')" />
    <UpdateModal ref="updateModal" :status="status" @status-updated="emit('status-updated', $event)" />
</template>
