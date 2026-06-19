<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import _ from 'lodash';
import { ref, watch } from 'vue';
import { Api, TrustedUserResource, UserProfileSettingsResource, UserResource } from '../../../../../types/Api.gen';
import SettingsListRow from '../../SettingsListRow.vue';

const props = defineProps<{
    profile: UserProfileSettingsResource;
    trustedUsers: TrustedUserResource[];
}>();
const emits = defineEmits(['profile-updated', 'friends-updated', 'error']);

const modal = ref<HTMLDialogElement>();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const users = ref<UserResource[]>([]);
const search = ref<string>('');
const showResults = ref<boolean>(false);
const loadingUsers = ref<boolean>(false);
const noUsersFound = ref<boolean>(false);

function removeUser(friend: TrustedUserResource) {
    api.user
        .trustedUserDestroy('self', friend.user.id)
        .then(() => {
            emits(
                'friends-updated',
                props.trustedUsers.filter((f) => f.user.id !== friend.user.id),
            );
        })
        .catch((error) => {
            emits('error', error.error.message);
        });
}

function fetchFriends() {
    noUsersFound.value = false;
    if (!search.value.trim()) {
        users.value.length = 0;
        showResults.value = false;
        return;
    }
    loadingUsers.value = true;
    api.user
        .searchUsers(search.value)
        .then((data) => {
            loadingUsers.value = false;
            if (!data.ok || data.status === 404) {
                users.value.length = 0;
                showResults.value = false;
                noUsersFound.value = true;
                return;
            }
            data.json().then((data) => {
                users.value.length = 0;
                const trustedUserIds = new Set(props.trustedUsers.map((u) => u.user.id));
                data.data = data.data.filter((u: UserResource) => !trustedUserIds.has(u.id));
                users.value.push(...data.data);
                showResults.value = users.value.length > 0;
                noUsersFound.value = users.value.length === 0;
            });
        })
        .catch(() => {
            users.value.length = 0;
            showResults.value = true;
            loadingUsers.value = false;
            noUsersFound.value = false;
        });
}

function selectFriend(user: UserResource) {
    api.user
        .trustedUserStore('self', { userId: user.id })
        .then(() => {
            const friend = {
                user: user,
                expiresAt: null,
            } as TrustedUserResource;
            emits('friends-updated', [...props.trustedUsers, friend]);
        })
        .catch((error) => {
            emits('error', error.error.message);
        });

    search.value = '';
    users.value.length = 0;
    showResults.value = false;
}

function handleBlur() {
    // Delay to allow click on result items to fire first
    setTimeout(() => {
        showResults.value = false;
    }, 200);
}

watch(search, _.debounce(fetchFriends, 500));
</script>

<template>
    <SettingsListRow :title="trans('settings.friend_checkin.list')" @click.prevent="modal?.showModal()" />
    <dialog ref="modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ trans('settings.friend_checkin.list') }}</h3>

            <input
                v-model="search"
                type="search"
                class="input w-full"
                autocomplete="off"
                :placeholder="trans('settings.find-users')"
                @focus="showResults = users.length > 0"
                @blur="handleBlur"
            />
            <div class="flex justify-center">
                <span v-if="loadingUsers" class="my-4 loading loading-spinner text-primary"></span>
                <span v-if="noUsersFound" class="my-4 text-error">
                    {{ trans('user.no-user') }}
                </span>
            </div>
            <ul v-if="showResults && users.length > 0" class="list">
                <li v-for="user in users" :key="user?.id" class="list-row hover:bg-base-200 cursor-pointer">
                    <div class="avatar">
                        <div class="rounded-full w-12 h-12">
                            <img :src="user.profilePicture" :alt="user.displayName" />
                        </div>
                    </div>
                    <div>
                        <div class="fw-bold">{{ user?.displayName }}</div>
                        <div class="text-muted small">{{ user?.username }}</div>
                    </div>
                    <button class="btn btn-sm btn-success" @click.prevent="selectFriend(user)">
                        <Plus class="w-4 h-4" />
                    </button>
                </li>
            </ul>
            <ul v-if="!showResults && users.length <= 0" class="list">
                <li v-for="friend in trustedUsers" :key="friend.user.id" class="list-row">
                    <div class="avatar">
                        <div class="rounded-full w-12 h-12">
                            <img :src="friend.user.profilePicture" :alt="friend.user.displayName" />
                        </div>
                    </div>

                    <div>
                        <h6 class="mb-0">
                            {{ friend.user.displayName }}
                        </h6>
                        <p class="mb-0 opacity-75">@{{ friend.user.username }}</p>
                    </div>
                    <button role="button" class="btn btn-sm btn-error" @click="removeUser(friend)">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </li>
            </ul>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn me-2">{{ trans('menu.close') }}</button>
                </form>
            </div>
        </div>
    </dialog>
</template>
