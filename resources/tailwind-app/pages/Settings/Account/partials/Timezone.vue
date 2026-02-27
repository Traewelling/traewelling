<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';
import { Api, UpdateProfileInformationRequest, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const timezones = ref<{ value: string; label: string }[]>([]);
const filteredTimezones = ref<{ value: string; label: string }[]>([]);
const input = ref<string>(props.profile.timezone);

timezones.value = Intl.supportedValuesOf('timeZone').map((timezone) => {
    return { value: timezone, label: timezone };
});

function updateTimezone() {
    const data = props.profile as UpdateProfileInformationRequest;
    data.timezone = input.value;
    api.settings.updateProfileSettings(data).then((response) => {
        response.json().then((data) => {
            emits('profile-updated', data.data);
        });
        modal.value?.close();
    });
}

watch(input, (newValue) => {
    if (newValue.length === 0) {
        filteredTimezones.value = [];
        return;
    }
    const searchLower = newValue.toLowerCase();
    filteredTimezones.value = timezones.value.filter((timezone) => {
        return timezone.label.toLowerCase().includes(searchLower);
    });
    // maximum 10 results
    filteredTimezones.value = filteredTimezones.value.slice(0, 10);
});
</script>

<template>
    <SettingsListRow :title="trans('user.timezone')" :badge="profile.timezone" @click="modal?.showModal()" />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ trans('user.timezone') }}</h3>
            <input
                v-model="input"
                type="text"
                :placeholder="trans('generic.search')"
                class="input input-bordered w-full mt-4"
            />
            <ul v-if="filteredTimezones.length > 0" class="menu bg-base-100 w-full mt-2 rounded-box shadow-md">
                <li v-for="timezone in filteredTimezones" :key="timezone.value">
                    <a @click.prevent="input = timezone.value">{{ timezone.label }}</a>
                </li>
            </ul>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    <button class="btn btn-primary" @click.prevent="updateTimezone()">
                        {{ trans('modals.edit-confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </dialog>
</template>
