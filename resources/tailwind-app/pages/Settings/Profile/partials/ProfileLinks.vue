<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, UserProfileSettingsResource } from '../../../../../types/Api.gen';
import { IconHelper } from '../../../../helpers/IconHelper';
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
};

const providerOptions: ProviderOption[] = [
    { label: 'Website', value: 'website' },
    { label: 'Instagram', value: 'instagram' },
    { label: 'BlueSky', value: 'bluesky' },
    { label: 'Facebook', value: 'facebook' },
    { label: 'TikTok', value: 'tiktok' },
    { label: 'GitHub', value: 'github' },
];

function saveLink() {
    const links = [...(props.profile.profileLinks ?? [])];
    const existingLinkIndex = links.findIndex((link) => link.name === selectedProvider.value?.value);
    if (existingLinkIndex !== -1) {
        links[existingLinkIndex] = { ...links[existingLinkIndex], url: input.value };
    } else {
        links.push({ name: selectedProvider.value!.value, url: input.value });
    }

    api.settings.updateProfileSettings({ profileLinks: links }).then((response) => {
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
    const links = (props.profile.profileLinks ?? []).filter((link) => link.name !== selectedProvider.value?.value);

    api.settings.updateProfileSettings({ profileLinks: links }).then((response) => {
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
    <SettingsListRow
        :title="trans('settings.profile.links')"
        :description="trans('settings.profile.links.description')"
        @click="modal?.showModal()"
    />
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
                                <component
                                    :is="IconHelper.getLinkIcon(option.value)"
                                    :class="option.value === 'website' ? '' : 'fill-base-content '"
                                    class="w-4 h-4 mr-2 stroke-base-content"
                                />
                                {{ option.label }}
                            </a>
                        </li>
                    </template>
                    <template v-else>
                        <li v-for="option in providerOptions" :key="option.value">
                            <a @click.prevent="selectProvider(option)">
                                <component
                                    :is="IconHelper.getLinkIcon(option.value)"
                                    :class="option.value === 'website' ? '' : 'fill-base-content '"
                                    class="w-4 h-4 mr-2 stroke-base-content"
                                />
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
