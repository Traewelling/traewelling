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

function reloadWindow() {
    window.location.reload();
}

function unblock() {
    api.user.destroyBlock(user.value.id).then(() => reloadWindow());
}

function block() {
    api.user.createBlock(user.value.id).then(() => reloadWindow());
}

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
    <a
        v-if="user.blocked"
        class="btn btn-sm btn-primary"
        data-bs-toggle="tooltip"
        :title="trans('user.unblock-tooltip')"
        @click.prevent="unblock()"
    >
        <i class="fas fa-unlock" aria-hidden="true"></i>
        <span class="visually-hidden">{{ trans('user.unblock-tooltip') }}</span>
    </a>
    <a
        v-else
        class="btn btn-sm btn-primary"
        data-bs-toggle="tooltip"
        :title="trans('user.block-tooltip')"
        @click.prevent="block()"
    >
        <i class="fas fa-ban" aria-hidden="true"></i>
        <span class="visually-hidden">{{ trans('user.block-tooltip') }}</span>
    </a>
</template>
