<script lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { defineComponent } from 'vue';
import { Api, FriendCheckinSetting, LightUserResource, TrustedUserResource, User } from '../../../types/Api.gen';
import { showApiValidationErrors } from '../../helpers/NotyfHelper';
import UserSearchDropdown from '../Helpers/UserSearchDropdown.vue';

// TODO: split this component into partials
export default defineComponent({
    name: 'FriendCheckinSettings',
    components: {
        UserSearchDropdown,
    },
    setup() {
        const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
        return { api };
    },
    data() {
        return {
            allow: FriendCheckinSetting.Forbidden,
            username: '',
            displayName: '',
            loading: false,
            trustedUsers: [] as TrustedUserResource[],
            privacyHideDays: null as number | null,
            email: '',
            notyf: new Notyf({
                position: { x: 'right', y: 'bottom' },
                duration: 2000,
                dismissible: true,
                ripple: true,
            }),
            FriendCheckinSetting,
        };
    },
    mounted() {
        this.fetchUserProfileSettings();
        this.fetchTrustedUsers();
    },
    methods: {
        trans,
        fetchUserProfileSettings() {
            this.loading = true;
            this.api.settings
                .getProfileSettings()
                .then((data) => {
                    if (!data.ok || data.status === 404) {
                        return;
                    }
                    data.json().then((data) => {
                        this.allow = data.data.friendCheckin;
                        this.username = data.data.username;
                        this.displayName = data.data.displayName;
                        this.privacyHideDays = data.data.privacyHideDays == 0 ? null : data.data.privacyHideDays;
                        this.email = data.data.email;
                        this.loading = false;
                    });
                })
                .catch(() => {});
        },
        fetchTrustedUsers() {
            this.api.user
                .trustedUserIndex('self')
                .then((data) => {
                    if (!data.ok || data.status === 404) {
                        this.trustedUsers = [];
                        return;
                    }
                    data.json().then((data) => {
                        this.trustedUsers = data.data;
                    });
                })
                .catch(() => {
                    this.trustedUsers = [];
                });
        },
        submit() {
            this.loading = true;
            this.api.settings
                .updateProfileSettings({
                    friendCheckin: this.allow,
                    username: this.username,
                    displayName: this.displayName,
                    privacyHideDays: this.privacyHideDays,
                    email: this.email,
                })
                .then((data) => {
                    this.loading = false;
                    if (data.status !== 200) {
                        this.notyf.error(trans('messages.exception.general'));
                        return;
                    }
                    this.notyf.success(trans('settings.saved'));
                })
                .catch((res) => {
                    this.loading = false;
                    if (res.status === 422) {
                        // Handle validation errors
                        showApiValidationErrors(this.notyf, res.error.errors);
                    } else {
                        this.notyf.error(res.error);
                    }
                });
        },
        removeUser(user: TrustedUserResource) {
            this.api.user
                .trustedUserDestroy('self', user.user.id)
                .then((data) => {
                    if (!data.ok) {
                        console.error(data);
                        return;
                    }
                    if (data.status === 204) {
                        this.trustedUsers = this.trustedUsers.filter((u) => u?.user?.id !== user?.user?.id);
                    }
                })
                .catch(() => {});
        },
        addUser(user: User) {
            this.api.user
                .trustedUserStore('self', { userId: user.id })
                .then((data) => {
                    if (data.status !== 201) {
                        this.notyf.error(trans('messages.exception.general'));
                        return;
                    }
                    this.addUserToList(user);
                })
                .catch(() => {
                    this.notyf.error(trans('messages.exception.general'));
                });
        },
        addUserToList(user: User) {
            // cast user to TrustedUserResource
            const lUser = {
                id: user.id,
                displayName: user.displayName,
                username: user.username,
                profilePicture: user.profilePicture,
            } as LightUserResource;
            const tUser = {
                user: lUser,
                expiresAt: '',
            } as TrustedUserResource;
            this.trustedUsers.push(tUser);
        },
    },
});
</script>

<template>
    <div class="card mb-3">
        <div class="card-header">
            {{ trans('settings.friend_checkin') }}
        </div>
        <div class="card-body">
            <form @submit.prevent="submit">
                <div class="form-group row">
                    <label for="allow-dropdown" class="col-md-4 col-form-label text-md-right">
                        {{ trans('settings.allow_friend_checkin_for') }}
                    </label>
                    <div class="col-md-6">
                        <select id="allow-dropdown" v-model="allow" class="form-select">
                            <option :value="FriendCheckinSetting.Forbidden">
                                {{ trans('settings.friend_checkin.forbidden') }}
                            </option>
                            <option :value="FriendCheckinSetting.Friends">
                                {{ trans('settings.friend_checkin.friends') }}
                            </option>
                            <option :value="FriendCheckinSetting.List">
                                {{ trans('settings.friend_checkin.list') }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-group row" />
                <hr />
                <div class="form-group row mb-0">
                    <div class="col-6 col-md-3 offset-md-4">
                        <button type="submit" class="btn btn-primary" :disabled="loading">
                            {{ trans('settings.btn-update') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            {{ trans('settings.friend_checkin.list') }}
        </div>
        <div class="card-body p-0">
            <div class="form-group row pb-3 mx-3 mt-2">
                <label for="allow-dropdown" class="col-md-4 col-form-label text-md-right">
                    {{ trans('settings.friend_checkin.add_user') }}
                </label>
                <div class="col-md-6">
                    <UserSearchDropdown @select-event="addUser" />
                </div>
            </div>

            <ul class="list-group list-group-flush border-top border-3 mb-3">
                <li
                    v-for="user in trustedUsers"
                    :key="user.user?.id"
                    class="list-group-item d-flex gap-3 py-3"
                    aria-current="true"
                >
                    <img
                        :src="user.user?.profilePicture"
                        alt="twbs"
                        width="32"
                        height="32"
                        class="rounded-circle flex-shrink-0"
                    />
                    <div class="d-flex gap-2 w-100 justify-content-between">
                        <div>
                            <h6 class="mb-0">
                                {{ user.user?.displayName }}
                            </h6>
                            <p class="mb-0 opacity-75">@{{ user.user?.username }}</p>
                        </div>
                        <button class="btn btn-sm btn-danger" @click="removeUser(user)">
                            <i class="fas fa-trash-alt" />
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
