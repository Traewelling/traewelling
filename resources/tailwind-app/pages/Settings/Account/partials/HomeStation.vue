<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, inject, ref } from 'vue';
import { StationResource } from '../../../../../types/Api.gen';
import { useUserStore } from '../../../../../vue/stores/user';
import StationAutocomplete from '../../../../components/StationAutocomplete.vue';
import SettingsListRow from '../../SettingsListRow.vue';

const notyf = inject('notyf') as Notyf;
const userStore = useUserStore();

const modal = ref<HTMLDialogElement>();
const saving = ref(false);

const homeStation = computed<StationResource | null>(() => userStore.getHome);

async function selectStation(station: StationResource): Promise<void> {
    saving.value = true;
    try {
        await userStore.setHome(station.uuid ?? station.id);
        notyf.success(trans('user.home-set', { Station: station.name }));
        modal.value?.close();
    } catch {
        notyf.error(trans('action.error') + ' (' + trans('user.home-station.set') + ')');
    } finally {
        saving.value = false;
    }
}

async function removeStation(): Promise<void> {
    saving.value = true;
    try {
        await userStore.deleteHome();
        notyf.success(trans('user.home-station.removed'));
        modal.value?.close();
    } catch {
        notyf.error(trans('action.error') + ' (' + trans('user.home-station.unset') + ')');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <SettingsListRow
        :title="trans('user.home-station')"
        :description="homeStation?.name ?? trans('user.home-not-set')"
        @click="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ trans('user.home-station') }}</h3>
            <p class="text-sm opacity-60 mt-1">{{ trans('user.home-station.description') }}</p>
            <div class="mt-4">
                <StationAutocomplete
                    :model-value="homeStation"
                    :small="false"
                    :show-history="false"
                    with-icon
                    @update:model-value="selectStation"
                />
            </div>
            <div class="modal-action">
                <button
                    v-if="homeStation"
                    class="btn btn-error btn-outline me-auto"
                    :disabled="saving"
                    @click="removeStation()"
                >
                    {{ trans('user.home-station.unset') }}
                </button>
                <form method="dialog">
                    <button class="btn">{{ trans('menu.abort') }}</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>
