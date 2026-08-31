<script setup lang="ts">
import { UserMinus, UserPlus, UserRoundX } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserResource } from '../../../../types/Api.gen';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import FollowersSubMenu from './partials/FollowersSubMenu.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });

const requests = ref<UserResource[]>([]);
const requestPage = ref<number>(1);
const hasMorePages = ref<boolean>(false);
const loading = ref(true);

const notyf = inject('notyf') as Notyf;

function getFollowers() {
    loading.value = true;
    api.user
        .getFollowRequests({ page: requestPage.value })
        .then((response) => {
            response.json().then((data) => {
                requestPage.value = data.meta.current_page + 1;
                hasMorePages.value = data.links.next !== null;
                requests.value.push(...data.data);
                loading.value = false;
            });
        })
        .catch((error) => {
            loading.value = false;
            error(error.error.message);
        });
}

function rejectFollower(follower: UserResource) {
    api.user
        .rejectFollowRequest(follower.id)
        .then(() => {
            requests.value = requests.value.filter((f) => f.id !== follower.id);
            notyf.success(trans('settings.request.reject-success'));
        })
        .catch((err) => {
            error(err.error.message);
        });
}

function acceptFollower(follower: UserResource) {
    api.user
        .acceptFollowRequest(follower.id)
        .then(() => {
            requests.value = requests.value.filter((f) => f.id !== follower.id);
            notyf.success(trans('settings.request.accept-success'));
        })
        .catch((err) => {
            error(err.error.message);
        });
}

function error(message: string): void {
    notyf.error(message);
}

getFollowers();
</script>

<template>
    <SettingsLayout>
        <FollowersSubMenu />
        <div class="mt-4">
            <div v-if="loading && requests.length === 0" class="flex justify-center py-12">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
            <div v-else-if="!loading && requests.length === 0" class="text-center py-12 text-base-content/50">
                <UserRoundX class="size-8 mx-auto mb-2" />
                <p>{{ trans('settings.follower.no-requests') }}</p>
            </div>
            <ul v-else class="list bg-base-100 rounded-box shadow-md mt-2">
                <li v-for="follower in requests" :key="follower.id" class="list-row items-center">
                    <div class="avatar">
                        <div class="rounded-full w-12 h-12">
                            <a :href="`/@${follower.username}`">
                                <img :src="follower.profilePicture" :alt="follower.displayName" />
                            </a>
                        </div>
                    </div>
                    <div class="list-col-grow">
                        <a :href="`/@${follower.username}`">
                            <h6 class="mb-0">{{ follower.displayName }}</h6>
                            <p class="mb-0 opacity-75">@{{ follower.username }}</p>
                        </a>
                    </div>
                    <button role="button" class="btn btn-sm btn-error" @click="rejectFollower(follower)">
                        <UserMinus class="w-4 h-4" />
                        <span class="hidden md:inline">{{ trans('settings.request.delete') }}</span>
                    </button>
                    <button role="button" class="btn btn-sm btn-success me-2" @click="acceptFollower(follower)">
                        <UserPlus class="w-4 h-4" />
                        <span class="hidden md:inline">{{ trans('settings.request.accept') }}</span>
                    </button>
                </li>
            </ul>
            <div v-if="hasMorePages" class="text-center w-full mt-4">
                <button class="btn" :disabled="loading" @click.prevent="getFollowers">
                    {{ loading ? trans('menu.loading') : trans('menu.show-more') }}
                </button>
            </div>
        </div>
    </SettingsLayout>
</template>
