<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, MapProvider, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const input = ref<MapProvider>(props.profile.mapProvider);

function updateMapProvider() {
    api.settings.updateProfileSettings({ mapProvider: input.value }).then((response) => {
        response.json().then((data) => {
            emits('profile-updated', data.data);
        });
        modal.value?.close();
    });
}
</script>

<template>
    <SettingsListRow
        :title="trans('user.mapprovider')"
        :description="trans('user.mapprovider.description')"
        @click="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ trans('user.mapprovider') }}</h3>
            <select v-model="input" class="select w-full mt-4">
                <option :value="MapProvider.Cargo">{{ trans('map-providers.cargo') }}</option>
                <option :value="MapProvider.OpenRailwayMap">{{ trans('map-providers.open-railway-map') }}</option>
            </select>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    <button class="btn btn-primary" @click.prevent="updateMapProvider()">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </dialog>
</template>
