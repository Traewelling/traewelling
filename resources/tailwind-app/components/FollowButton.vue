<script setup lang="ts">
import { UserMinus, UserPlus } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, UserResource } from '../../types/Api.gen';
import { useUserStore } from '../../vue/stores/user';

const props = defineProps<{
    userData: UserResource;
}>();

const emit = defineEmits<{
    'update:userData': [user: UserResource];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;
const authUser = useUserStore();
const busy = ref(false);

async function follow() {
    busy.value = true;
    try {
        await api.user.createFollow(props.userData.id);
        if (props.userData.privateProfile) {
            emit('update:userData', { ...props.userData, followPending: true });
        } else {
            emit('update:userData', { ...props.userData, following: true });
        }
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        busy.value = false;
    }
}

async function unfollow() {
    busy.value = true;
    try {
        await api.user.destroyFollow(props.userData.id);
        emit('update:userData', { ...props.userData, following: false });
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <RouterLink
        v-if="userData.id === authUser.getId"
        :to="{ name: 'settings' }"
        class="btn btn-sm btn-outline text-primary-content border-primary-content/40 hover:bg-primary-content/10 hover:border-primary-content/60 hover:text-primary-content"
    >
        {{ trans('profile.settings') }}
    </RouterLink>

    <button v-else-if="userData.following" class="btn btn-sm btn-error" :disabled="busy" @click="unfollow">
        <UserMinus class="w-4 h-4" />
        {{ trans('profile.unfollow') }}
    </button>

    <button
        v-else-if="userData.followPending"
        class="btn btn-sm btn-outline text-primary-content border-primary-content/40 hover:bg-primary-content/10 hover:border-primary-content/60 hover:text-primary-content"
        disabled
    >
        {{ trans('profile.follow_req.pending') }}
    </button>

    <button
        v-else-if="userData.privateProfile"
        class="btn btn-sm btn-outline text-primary-content border-primary-content/40 hover:bg-primary-content/10 hover:border-primary-content/60 hover:text-primary-content"
        :disabled="busy"
        @click="follow"
    >
        <UserPlus class="w-4 h-4" />
        {{ trans('profile.follow_req') }}
    </button>

    <button
        v-else
        class="btn btn-sm btn-outline text-primary-content border-primary-content/40 hover:bg-primary-content/10 hover:border-primary-content/60 hover:text-primary-content"
        :disabled="busy"
        @click="follow"
    >
        <UserPlus class="w-4 h-4" />
        {{ trans('profile.follow') }}
    </button>
</template>
