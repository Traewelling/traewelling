<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, UpdateProfileInformationRequest, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
}>();
const emits = defineEmits(['profile-updated']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const searchInput = ref<string>('');
const input = ref<string>('');
const selectedProvider = ref<ProviderOption | null>(null);

type ProviderOption = {
    label: string;
    value: 'website' | 'instagram' | 'bluesky' | 'facebook' | 'mastodon' | 'tiktok' | 'github';
    icon: string;
};

const providerOptions: ProviderOption[] = [
    { label: 'Website', value: 'website', icon: '' },
    { label: 'Instagram', value: 'instagram', icon: '/images/icons/instagram.svg' },
    { label: 'BlueSky', value: 'bluesky', icon: '/images/icons/bluesky.svg' },
    { label: 'Facebook', value: 'facebook', icon: '/images/icons/facebook.svg' },
    { label: 'TikTok', value: 'tiktok', icon: '/images/icons/tiktok.svg' },
    { label: 'GitHub', value: 'github', icon: '/images/icons/github.svg' },
];

function saveLink() {
    const data = props.profile as UpdateProfileInformationRequest;
    data.profileLinks = data.profileLinks || [];
    const existingLinkIndex = data.profileLinks.findIndex((link) => link.name === selectedProvider.value?.value);
    if (existingLinkIndex !== -1) {
        data.profileLinks[existingLinkIndex].url = input.value;
    } else {
        data.profileLinks.push({
            name: selectedProvider.value!.value,
            url: input.value,
        });
    }

    api.settings.updateProfileSettings(data).then((response) => {
        response.json().then((data) => {
            selectedProvider.value = null;
            emits('profile-updated', data.data);
        });
        modal.value?.close();
    });
}

function selectProvider(option: ProviderOption) {
    selectedProvider.value = option;
    const existingLink = props.profile.profileLinks?.find((link) => link.name === option.value);
    input.value = existingLink?.url || '';
}

function getCurrentLink(providerValue: string): string {
    return props.profile.profileLinks?.find((link) => link.name === providerValue)?.url || '';
}

function removeLink(): void {
    const data = props.profile as UpdateProfileInformationRequest;
    data.profileLinks = data.profileLinks || [];
    data.profileLinks = data.profileLinks.filter((link) => link.name !== selectedProvider.value?.value);

    api.settings.updateProfileSettings(data).then((response) => {
        response.json().then((data) => {
            selectedProvider.value = null;
            input.value = '';
            emits('profile-updated', data.data);
        });
        modal.value?.close();
    });
}
</script>

<template>
    <SettingsListRow :title="trans('settings.profile.links')" @click="modal?.showModal()" />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <template v-if="selectedProvider === null">
                <h3 class="text-lg font-bold">{{ trans('settings.profile.links') }}</h3>
                <input
                    v-model="searchInput"
                    type="text"
                    :placeholder="trans('generic.search')"
                    class="input input-bordered w-full mt-4"
                />
                <ul class="menu bg-base-100 w-full mt-2 rounded-box">
                    <template v-if="searchInput.length > 0">
                        <li
                            v-for="option in providerOptions.filter((o) =>
                                o.label.toLowerCase().includes(searchInput.toLowerCase()),
                            )"
                            :key="option.value"
                        >
                            <a @click.prevent="selectProvider(option)">
                                <img v-if="option.icon" :src="option.icon" alt="" class="w-4 h-4 me-2 inline" />
                                {{ option.label }}
                            </a>
                        </li>
                    </template>
                    <template v-else>
                        <li v-for="option in providerOptions" :key="option.value">
                            <a @click.prevent="selectProvider(option)">
                                <img v-if="option.icon" :src="option.icon" alt="" class="w-4 h-4 me-2 inline" />
                                {{ option.label }}
                                <span
                                    v-if="getCurrentLink(option.value).length > 0"
                                    class="badge badge-xs badge-outline text-xs"
                                >
                                    {{ getCurrentLink(option.value) }}
                                </span>
                            </a>
                        </li>
                    </template>
                </ul>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn me-2">{{ trans('menu.abort') }}</button>
                    </form>
                </div>
            </template>
            <template v-else>
                <form @submit.prevent="saveLink()">
                    <h3 class="text-lg font-bold">{{ selectedProvider.label }}</h3>
                    <input
                        v-model="input"
                        type="url"
                        :placeholder="trans('settings.profile.link-placeholder')"
                        class="input input-bordered w-full mt-4"
                        required
                        tabindex="-3"
                    />
                    <div class="modal-action">
                        <button
                            v-if="getCurrentLink(selectedProvider.value)"
                            type="button"
                            class="btn me-auto btn-outline btn-error"
                            @click.prevent="removeLink()"
                        >
                            {{ trans('delete') }}
                        </button>
                        <button
                            type="button"
                            class="btn me-2"
                            tabindex="-1"
                            @click="
                                selectedProvider = null;
                                input = '';
                            "
                        >
                            {{ trans('menu.abort') }}
                        </button>
                        <button type="submit" class="btn btn-primary" tabindex="-2">
                            {{ trans('modals.edit-confirm') }}
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </dialog>
</template>
