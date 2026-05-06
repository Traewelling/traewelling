<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, MastodonVisibility, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const input = ref<MastodonVisibility>(props.profile.mastodonVisibility);

const visibilities = [
    {
        value: MastodonVisibility.Value0,
        label: 'settings.mastodon.visibility.0',
    },
    {
        value: MastodonVisibility.Value1,
        label: 'settings.mastodon.visibility.1',
    },
    {
        value: MastodonVisibility.Value2,
        label: 'settings.mastodon.visibility.2',
    },
    {
        value: MastodonVisibility.Value3,
        label: 'settings.mastodon.visibility.3',
    },
];

function getLabel(visibility: MastodonVisibility): string {
    const option = visibilities.find((v) => v.value === visibility);
    return option ? trans(option.label) : '';
}

function updateVisibility() {
    api.settings
        .updateProfileSettings({ mastodonVisibility: input.value })
        .then((response) => {
            response.json().then((data) => {
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
        :title="trans('settings.mastodon.visibility')"
        :description="trans('settings.mastodon.visibility.description')"
        :badge="getLabel(profile.mastodonVisibility)"
        @click.prevent="modal?.showModal()"
    />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="updateVisibility">
                <h3 class="text-lg font-bold">{{ trans('settings.mastodon.visibility') }}</h3>
                <select v-model="input" class="select w-full">
                    <option v-for="option in visibilities" :key="option.value" :value="option.value">
                        {{ trans(option.label) }}
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
