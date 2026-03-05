<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { UserMinus, UserPlus, UserRoundX } from 'lucide-vue-next';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserResource } from '../../../../types/Api.gen';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import FollowersSubMenu from './partials/FollowersSubMenu.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

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
        <ul v-if="requests" class="list bg-base-100 rounded-box shadow-md mt-2">
            <li v-for="follower in requests" :key="follower.id" class="list-row items-center">
                <div class="flex items-center gap-3">
                    <div class="avatar">
                        <div class="rounded-full w-12 h-12">
                            <img :src="follower.profilePicture" :alt="follower.displayName" />
                        </div>
                    </div>
                </div>

                <div class="list-col-grow">
                    <h6 class="mb-0">
                        {{ follower.displayName }}
                    </h6>
                    <p class="mb-0 opacity-75">@{{ follower.username }}</p>
                </div>
                <button role="button" class="btn btn-sm btn-error" @click="rejectFollower(follower)">
                    <UserMinus class="w-4 h-4" />
                    <span class="hidden md:inline">
                        {{ trans('settings.request.delete') }}
                    </span>
                </button>
                <button role="button" class="btn btn-sm btn-success me-2" @click="acceptFollower(follower)">
                    <UserPlus class="w-4 h-4" />
                    <span class="hidden md:inline">
                        {{ trans('settings.request.accept') }}
                    </span>
                </button>
            </li>
        </ul>
        <div class="flex justify-center">
            <span v-if="loading" class="my-4 loading loading-spinner text-primary">
                {{ trans('menu.loading') }}
            </span>
            <span v-if="!loading && requests.length <= 0" class="my-4 text-error">
                <UserRoundX class="size-5 inline" />
                {{ trans('settings.follower.no-requests') }}
            </span>
        </div>
        <div v-if="hasMorePages" class="text-center w-full mt-4">
            <button class="btn" :disabled="loading" @click.prevent="getFollowers">
                {{ loading ? trans('menu.loading') : trans('menu.show-more') }}
            </button>
        </div>
    </SettingsLayout>
</template>
