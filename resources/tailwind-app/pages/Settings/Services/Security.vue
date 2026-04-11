<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, IcsEntryResource, UserProfileSettingsResource } from '../../../../types/Api.gen';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import AddIcsToken from './partials/AddIcsToken.vue';
import ManageIcsTokens from './partials/ManageIcsTokens.vue';
import ManageWebhooks from './partials/ManageWebhooks.vue';
import MastodonAccount from './partials/MastodonAccount.vue';
import Sessions from './partials/Sessions.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const profile = ref<UserProfileSettingsResource | null>(null);
const tokens = ref<IcsEntryResource[]>([]);
const loading = ref(true);

const notyf = inject('notyf') as Notyf;

function getUserProfile() {
    api.settings.getProfileSettings().then((response) => {
        response.json().then((data) => {
            profile.value = data.data;
            loading.value = false;
        });
    });
}

function getIcsTokens() {
    api.icsTokens
        .getIcsTokens()
        .then((response) => {
            if (!response.ok || response.status === 404) {
                tokens.value = [];
                return;
            }
            response.json().then((data) => {
                tokens.value = data.data;
            });
        })
        .catch((error) => {
            notyf.error(error.error.message);
            tokens.value = [];
        });
}

function error(message: string): void {
    notyf.error(message);
}

function updateTokens(newTokens: IcsEntryResource[]): void {
    tokens.value = newTokens;
    notyf.success(trans('settings.saved'));
}

function tokenAdded(): void {
    notyf.success(trans('settings.saved'));
    getIcsTokens();
}

function mastodonRemoved(): void {
    notyf.success(trans('settings.saved'));
    getUserProfile();
}

getIcsTokens();
getUserProfile();
</script>

<template>
    <SettingsLayout>
        <h2 class="text-xl font-bold">{{ trans('settings.title-loginservices') }}</h2>
        <ul v-if="!loading && profile" class="list bg-base-100 rounded-box shadow-md mt-2">
            <MastodonAccount :profile="profile" @mastodon-removed="mastodonRemoved()" />
        </ul>

        <h2 class="text-xl font-bold mt-4">{{ trans('settings.title-ics') }}</h2>
        <ul class="list bg-base-100 rounded-box shadow-md mt-2">
            <AddIcsToken @ics-added="tokenAdded()" @error="error" />
            <ManageIcsTokens :tokens="tokens" @ics-updated="updateTokens" @error="error" />
        </ul>

        <h2 class="text-xl font-bold mt-4">{{ trans('settings.title-security') }}</h2>
        <ul class="list bg-base-100 rounded-box shadow-md mt-2">
            <Sessions />
            <ManageWebhooks @error="error" />
        </ul>
    </SettingsLayout>
</template>
