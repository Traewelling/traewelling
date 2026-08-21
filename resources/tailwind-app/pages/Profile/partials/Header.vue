<script setup lang="ts">
import { Ban, Flag, Lock, ShieldCogCorner, UserCheck, Volume2, VolumeX } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, PropType, ref } from 'vue';
import { Api, UserResource, ViewUserForbiddenReason } from '../../../../types/Api.gen';
import { useUserStore } from '../../../../vue/stores/user';
import FollowButton from '../../../components/FollowButton.vue';
import ReportModal from '../../../components/ReportModal.vue';

const props = defineProps({
    userData: {
        type: Object as PropType<UserResource>,
        required: true,
    },
    userInvisibleReason: {
        type: Object as PropType<ViewUserForbiddenReason | null>,
        required: false,
        default: null,
    },
});

const emit = defineEmits<{
    (e: 'update:userData', userData: UserResource): void;
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;
const showReportModal = ref(false);
const authUser = useUserStore();

const busyMute = ref(false);
const busyBlock = ref(false);

async function toggleMute() {
    if (!props.userData) return;
    busyMute.value = true;
    try {
        if (props.userData.muted) {
            const data = await api.user.destroyMute(props.userData.id);
            emit('update:userData', data.data.data);
        } else {
            const data = await api.user.createMute(props.userData.id);
            emit('update:userData', data.data.data);
        }
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        busyMute.value = false;
    }
}

async function toggleBlock() {
    if (!props.userData) return;
    busyBlock.value = true;
    try {
        if (props.userData.blocked) {
            const data = await api.user.destroyBlock(props.userData.id);
            emit('update:userData', data.data.data);
        } else {
            const data = await api.user.createBlock(props.userData.id.toString());
            emit('update:userData', data.data.data);
        }
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        busyBlock.value = false;
    }
}
</script>

<template>
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
        <img
            :src="userData.profilePicture"
            :alt="userData.displayName"
            class="w-20 h-20 rounded-full border-4 border-primary-content/20 shrink-0 object-cover"
        />
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold flex items-center gap-2 leading-tight">
                {{ userData.displayName }}
                <Lock v-if="userData.privateProfile" class="w-5 h-5 opacity-60 shrink-0" />
            </h1>
            <p class="text-sm opacity-70 mt-0.5">
                @{{ userData.username }}
                <span v-if="userData.followedBy" class="badge badge-success badge-sm ml-1 align-middle">
                    {{ trans('profile.follows-you') }}
                </span>
            </p>

            <!-- Action buttons -->
            <div v-if="authUser.authenticated" class="flex flex-wrap gap-2 mt-3">
                <FollowButton :user-data="userData" @update:user-data="emit('update:userData', $event)" />

                <template v-if="userData.id !== authUser.getId">
                    <!-- Mute / Unmute (not when blocked by them) -->
                    <button
                        v-if="userInvisibleReason !== ViewUserForbiddenReason.YOU_ARE_BLOCKED"
                        class="btn btn-sm btn-ghost text-primary-content/70 hover:bg-primary-content/10 hover:text-primary-content tooltip"
                        :data-tip="userData.muted ? trans('user.unmute-tooltip') : trans('user.mute-tooltip')"
                        :disabled="busyMute"
                        @click="toggleMute"
                    >
                        <VolumeX v-if="!userData.muted" class="size-4">
                            <title>{{ trans('user.unmute-tooltip') }}</title>
                        </VolumeX>
                        <Volume2 v-else class="size-4">
                            <title>{{ trans('user.mute-tooltip') }}</title>
                        </Volume2>
                    </button>

                    <!-- Block / Unblock -->
                    <button
                        class="btn btn-sm btn-ghost text-primary-content/70 hover:bg-primary-content/10 hover:text-primary-content tooltip"
                        :data-tip="userData.blocked ? trans('user.unblock-tooltip') : trans('user.block-tooltip')"
                        :disabled="busyBlock"
                        @click="toggleBlock"
                    >
                        <UserCheck v-if="userData.blocked" class="size-4">
                            <title>{{ trans('user.unblock-tooltip') }}</title>
                        </UserCheck>
                        <Ban v-else class="size-4">
                            <title>{{ trans('user.block-tooltip') }}</title>
                        </Ban>
                    </button>

                    <!-- Report -->
                    <button
                        class="btn btn-sm btn-ghost text-primary-content/70 hover:bg-primary-content/10 hover:text-primary-content tooltip"
                        :data-tip="trans('status.report')"
                        @click="showReportModal = true"
                    >
                        <Flag class="size-4">
                            <title>{{ trans('status.report') }}</title>
                        </Flag>
                    </button>

                    <!-- Admin link -->
                    <a
                        v-if="authUser.isAdmin"
                        :href="`/admin/users/${userData.id}`"
                        class="btn btn-sm btn-ghost text-primary-content/70 hover:bg-primary-content/10 hover:text-primary-content tooltip"
                        :data-tip="trans('menu.admin')"
                    >
                        <ShieldCogCorner class="size-4">
                            <title>{{ trans('menu.admin') }}</title>
                        </ShieldCogCorner>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <ReportModal
        :open="showReportModal"
        subject-type="User"
        :subject-id="userData.id"
        @close="showReportModal = false"
    />
</template>
