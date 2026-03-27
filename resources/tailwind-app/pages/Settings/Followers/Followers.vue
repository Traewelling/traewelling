<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { UserMinus, UserRoundX } from 'lucide-vue-next';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserResource } from '../../../../types/Api.gen';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';
import FollowersSubMenu from './partials/FollowersSubMenu.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const followers = ref<UserResource[]>([]);
const requestPage = ref<number>(1);
const hasMorePages = ref<boolean>(false);
const loading = ref(true);

const notyf = inject('notyf') as Notyf;

function getFollowers() {
    loading.value = true;
    api.user
        .getFollowers({ page: requestPage.value })
        .then((response) => {
            response.json().then((data) => {
                requestPage.value = data.meta.current_page + 1;
                hasMorePages.value = data.links.next !== null;
                followers.value.push(...data.data);
                loading.value = false;
            });
        })
        .catch((error) => {
            loading.value = false;
            error(error.error.message);
        });
}

function removeUser(follower: UserResource) {
    api.user
        .removeFollower(follower.id)
        .then(() => {
            followers.value = followers.value.filter((f) => f.id !== follower.id);
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
        <ul v-if="followers" class="list bg-base-100 rounded-box shadow-md mt-2">
            <li v-for="follower in followers" :key="follower.id" class="list-row items-center">
                <div class="flex items-center gap-3">
                    <div class="avatar">
                        <div class="rounded-full w-12 h-12">
                            <a :href="`/@${follower.username}`">
                                <img :src="follower.profilePicture" :alt="follower.displayName" />
                            </a>
                        </div>
                    </div>
                </div>

                <div class="list-col-grow">
                    <a :href="`/@${follower.username}`">
                        <h6 class="mb-0">
                            {{ follower.displayName }}
                        </h6>
                        <p class="mb-0 opacity-75">@{{ follower.username }}</p>
                    </a>
                </div>
                <button role="button" class="btn btn-sm btn-error" @click="removeUser(follower)">
                    <UserMinus class="w-4 h-4" />
                    {{ trans('settings.follower.delete') }}
                </button>
            </li>
        </ul>
        <div class="flex justify-center">
            <span v-if="loading" class="my-4 loading loading-spinner text-primary">
                {{ trans('menu.loading') }}
            </span>
            <span v-if="!loading && followers.length <= 0" class="my-4 text-error">
                <UserRoundX class="size-5 inline" />
                {{ trans('settings.follower.no-follower') }}
            </span>
        </div>
        <div v-if="hasMorePages" class="text-center w-full mt-4">
            <button class="btn" :disabled="loading" @click.prevent="getFollowers">
                {{ loading ? trans('menu.loading') : trans('menu.show-more') }}
            </button>
        </div>
    </SettingsLayout>
</template>
