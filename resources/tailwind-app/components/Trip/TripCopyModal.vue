<script setup lang="ts">
import { TriangleAlert } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, inject, ref } from 'vue';
import { Api, TripResource } from '../../../types/Api.gen';
import { useUserStore } from '../../../vue/stores/user';

const props = defineProps<{
    open: boolean;
    tripUuid: string;
    points: number;
}>();

const emit = defineEmits<{
    close: [];
    copied: [trip: TripResource];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;
const userStore = useUserStore();

const copying = ref(false);

const showPointsWarning = computed(() => (userStore.user?.pointsEnabled ?? false) && props.points > 0);

async function confirm(): Promise<void> {
    copying.value = true;
    try {
        const response = await api.trips.copyTrip(props.tripUuid);
        emit('copied', response.data.data as TripResource);
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        copying.value = false;
    }
}
</script>

<template>
    <dialog class="modal" :class="{ 'modal-open': open }">
        <div class="modal-box">
            <h3 class="font-bold text-lg">{{ trans('trip.copy.title') }}</h3>
            <p class="pt-3 text-sm">{{ trans('trip.copy.description') }}</p>
            <p class="py-3 text-sm text-base-content/60">{{ trans('trip.copy.creator-hint') }}</p>

            <div v-if="showPointsWarning" role="alert" class="alert alert-warning text-sm">
                <TriangleAlert class="size-4 shrink-0" />
                <span>{{ trans('trip.copy.points-warning', { points: points.toString() }) }}</span>
            </div>

            <div class="modal-action">
                <button class="btn btn-ghost" :disabled="copying" @click="emit('close')">
                    {{ trans('cancel') }}
                </button>
                <button class="btn btn-primary" :disabled="copying" @click="confirm">
                    <span v-if="copying" class="loading loading-spinner loading-xs" />
                    {{ trans('trip.copy.confirm') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @submit.prevent="emit('close')">
            <button>close</button>
        </form>
    </dialog>
</template>
