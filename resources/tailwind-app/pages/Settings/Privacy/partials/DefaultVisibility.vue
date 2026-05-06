<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, StatusVisibility, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import { useProfileSettingsStore } from '../../../../../vue/stores/profileSettings';
import { VISIBILITY_ITEMS } from '../../../../helpers/visibility';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const profileStore = useProfileSettingsStore();
const input = ref<StatusVisibility>(props.profile.defaultStatusVisibility);

function getLabel(visibility: StatusVisibility): string {
    const item = VISIBILITY_ITEMS.find((v) => v.value === visibility);
    return item ? trans(item.labelKey) : '';
}

function updateVisibility() {
    api.settings
        .updateProfileSettings({ defaultStatusVisibility: input.value })
        .then((response) => {
            response.json().then((data) => {
                profileStore.updateDefaultStatusVisibility(input.value);
                emits('profile-updated', data.data);
            });
            modal.value?.close();
        })
        .catch((error) => {
            emits('error', error.error.message);
            modal.value?.close();
        });
}
</script>

<template>
    <SettingsListRow
        :title="trans('settings.visibility.default')"
        :description="trans('settings.visibility.default.description')"
        :badge="getLabel(profile.defaultStatusVisibility)"
        @click.prevent="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="updateVisibility">
                <h3 class="text-lg font-bold">{{ trans('settings.visibility.default') }}</h3>
                <select v-model="input" class="select w-full">
                    <option v-for="item in VISIBILITY_ITEMS" :key="item.value" :value="item.value">
                        {{ trans(item.labelKey) }} ({{ trans(item.detailKey) }})
                    </option>
                </select>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    </form>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
