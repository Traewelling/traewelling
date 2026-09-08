<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';
import { Api, UserResource } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api' });

const props = defineProps<{
    userData: UserResource;
}>();

const user = ref<UserResource>(props.userData);

function reloadWindow() {
    window.location.reload();
}

function unmute() {
    api.user.destroyMute(user.value.id).then(() => reloadWindow());
}

function mute() {
    api.user.createMute(user.value.id).then(() => reloadWindow());
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
        v-if="user.muted"
        class="btn btn-sm btn-primary"
        data-bs-toggle="tooltip"
        :title="trans('user.unmute-tooltip')"
        @click.prevent="unmute()"
    >
        <i class="fas fa-eye" aria-hidden="true"></i>
        <span class="visually-hidden">{{ trans('user.unmute-tooltip') }}</span>
    </a>
    <a
        v-else
        class="btn btn-sm btn-primary"
        data-bs-toggle="tooltip"
        :title="trans('user.mute-tooltip')"
        @click.prevent="mute()"
    >
        <i class="fas fa-eye-slash" aria-hidden="true"></i>
        <span class="visually-hidden">{{ trans('user.mute-tooltip') }}</span>
    </a>
</template>
