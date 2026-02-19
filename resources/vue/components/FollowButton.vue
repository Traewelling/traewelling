<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';
import { Api, UserResource } from '../../types/Api.gen';
import { useUserStore } from '../stores/user';

const authUser = useUserStore();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const props = defineProps<{
    userData: UserResource;
}>();

const user = ref<UserResource>(props.userData);

watch(
    () => props.userData,
    (newUser) => {
        if (newUser) {
            user.value = newUser;
        }
    },
    { immediate: true },
);
</script>

<template>
    <a v-if="user.id === authUser.getId" href="/settings" class="btn btn-sm btn-primary">
        {{ trans('profile.settings') }}
    </a>
    <a
        v-else-if="user.privateProfile && user.followPending"
        href="#"
        class="btn btn-sm btn-primary disabled"
        aria-disabled="true"
    >
        {{ trans('profile.follow_req.pending') }}
    </a>
    <a
        v-else-if="user.privateProfile && !user.following"
        href="#"
        class="btn btn-sm btn-primary follow"
        @click.prevent="api.user.createFollow(user.id).then(() => (user.followPending = true))"
    >
        {{ trans('profile.follow_req') }}
    </a>
    <a
        v-else-if="!user.following"
        href="#"
        class="btn btn-sm btn-primary follow"
        @click.prevent="api.user.createFollow(user.id).then(() => (user.following = true))"
    >
        {{ trans('profile.follow') }}
    </a>
    <a
        v-else
        href="#"
        class="btn btn-sm btn-danger follow"
        @click.prevent="api.user.destroyFollow(user.id).then(() => (user.following = false))"
    >
        {{ trans('profile.unfollow') }}
    </a>
</template>
