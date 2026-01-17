<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { ref } from 'vue';
import {
    Api,
    FriendCheckinSetting,
    MapProvider,
    MastodonVisibility,
    StatusVisibility,
    UpdateProfileInformationRequest,
    UserProfileSettingsResource,
} from '../../../types/Api.gen';
import { showApiValidationErrors } from '../../helpers/NotyfHelper';
import { useUserStore } from '../../stores/user';
import Input from './Partials/Input.vue';
import Select from './Partials/Select.vue';
import { SelectOption } from './Partials/SelectOption';
import Textfield from './Partials/Textfield.vue';
import TimezoneDropdown from './Partials/TimezoneDropdown.vue';
const userStore = useUserStore();
const notyf = new Notyf({ position: { x: 'right', y: 'bottom' } });
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const timezones = Intl.supportedValuesOf('timeZone').map((timezone) => {
    return { value: timezone, label: timezone } as SelectOption;
});
const providers = Object.values(MapProvider).map((provider) => {
    return { value: provider, translationKey: `map-providers.${provider}` } as SelectOption;
});
const errors = ref({} as any);
const userData = ref({
    username: '',
    displayName: '',
    privateProfile: false,
    preventIndex: false,
    privacyHideDays: 1,
    defaultStatusVisibility: StatusVisibility.Value0,
    mastodonVisibility: MastodonVisibility.Value0,
    mapProvider: MapProvider.Cargo,
    friendCheckin: FriendCheckinSetting.Value0,
    likesEnabled: false,
    pointsEnabled: false,
    bio: '',
    experimental: false,
    profileLinks: [],
    email: '',
    timezone: '',
} as UpdateProfileInformationRequest);

const mapData = (data: UserProfileSettingsResource) => {
    return {
        username: data.username,
        displayName: data.displayName,
        privateProfile: data.privateProfile,
        preventIndex: data.preventIndex,
        privacyHideDays: data.privacyHideDays == 0 ? null : data.privacyHideDays,
        defaultStatusVisibility: data.defaultStatusVisibility,
        mastodonVisibility: data.mastodonVisibility,
        mapProvider: data.mapProvider,
        friendCheckin: data.friendCheckin,
        likesEnabled: data.likesEnabled,
        pointsEnabled: data.pointsEnabled,
        bio: data.bio,
        experimental: data.experimental,
        profileLinks: data.profileLinks,
        email: data.email,
        timezone: data.timezone,
    } as UpdateProfileInformationRequest;
};

const getDefaultUserData = () => {
    api.settings
        .getProfileSettings()
        .then((res) => {
            if (res.ok && res.data.data !== undefined) {
                userData.value = mapData(res.data.data);
            }
        })
        .catch(() => {});
};

const updateProfile = () => {
    errors.value = {};
    api.settings
        .updateProfileSettings(userData.value)
        .then((res) => {
            if (res.ok) {
                userData.value = mapData(res.data.data);
                userStore.fetchSettings(true);
                notyf.success(trans('settings.saved'));
            }
        })
        .catch((res) => {
            if (res.status === 422) {
                // Handle validation errors
                errors.value = res.error.errors;
                // foreach error and show it
                showApiValidationErrors(notyf, errors.value);
            } else {
                notyf.error(trans('generic.error'));
            }
        });
};

getDefaultUserData();
</script>

<template>
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header">
                {{ trans('settings.title-profile') }}
            </div>

            <div class="card-body">
                <form class="d-grid gap-1" @submit.prevent="updateProfile">
                    <Input
                        v-model="userData.username"
                        name="username"
                        prefix="@"
                        :errors="errors.username"
                        :title="trans('user.username')"
                    />
                    <Input
                        v-model="userData.displayName"
                        name="name"
                        :errors="errors.displayName"
                        :title="trans('user.displayname')"
                        autocomplete="name"
                    />
                    <Textfield
                        v-model="userData.bio"
                        name="bio"
                        :errors="errors.bio"
                        :title="trans('profile.bio')"
                        :placeholder="trans('profile.bio')"
                    />
                    <Input
                        v-model="userData.email"
                        name="email"
                        :errors="errors.email"
                        :title="trans('user.email')"
                        autocomplete="email"
                        required="true"
                    />
                    <TimezoneDropdown v-model="userData.timezone" />

                    <Select
                        v-model="userData.experimental"
                        :title="trans('settings.experimental')"
                        :name="'experimental'"
                        :options="[
                            { value: true, translationKey: 'settings.allow' },
                            { value: false, translationKey: 'settings.prevent' },
                        ]"
                        :errors="errors.experimental"
                    />

                    <div class="form-group row mt-3">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                {{ trans('settings.btn-update') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
