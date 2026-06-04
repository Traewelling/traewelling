<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Ban, Flag, Lock, UserCheck, Volume2, VolumeX } from 'lucide-vue-next';
import { Notyf } from 'notyf';
import { inject, PropType, ref } from 'vue';
import { Api, UserResource, ViewUserForbiddenReason } from '../../../../types/Api.gen';
import { useUserStore } from '../../../../vue/stores/user';
import FollowButton from '../../../components/FollowButton.vue';

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

const reportReason = ref<'inappropriate' | 'implausible' | 'spam' | 'illegal' | 'other' | ''>('');
const reportDescription = ref('');
const reportLoading = ref(false);

const busyMute = ref(false);
const busyBlock = ref(false);

async function submitReport() {
    if (!props.userData || !reportReason.value) return;
    reportLoading.value = true;
    try {
        await api.reports.createReport({
            subjectType: 'User',
            subjectId: props.userData.id,
            reason: reportReason.value,
            description: reportDescription.value,
        });
        notyf?.success(trans('report.success'));
        showReportModal.value = false;
        reportReason.value = '';
        reportDescription.value = '';
    } catch {
        notyf?.error(trans('report.error'));
    } finally {
        reportLoading.value = false;
    }
}

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
                        <VolumeX v-if="!userData.muted" class="w-4 h-4" />
                        <Volume2 v-else class="w-4 h-4" />
                    </button>

                    <!-- Block / Unblock -->
                    <button
                        class="btn btn-sm btn-ghost text-primary-content/70 hover:bg-primary-content/10 hover:text-primary-content tooltip"
                        :data-tip="userData.blocked ? trans('user.unblock-tooltip') : trans('user.block-tooltip')"
                        :disabled="busyBlock"
                        @click="toggleBlock"
                    >
                        <UserCheck v-if="userData.blocked" class="w-4 h-4" />
                        <Ban v-else class="w-4 h-4" />
                    </button>

                    <!-- Report -->
                    <button
                        class="btn btn-sm btn-ghost text-primary-content/70 hover:bg-primary-content/10 hover:text-primary-content tooltip"
                        :data-tip="trans('status.report')"
                        @click="showReportModal = true"
                    >
                        <Flag class="w-4 h-4" />
                    </button>

                    <!-- Admin link -->
                    <a
                        v-if="authUser.isAdmin"
                        :href="`/admin/users/${userData.id}`"
                        class="btn btn-sm btn-ghost text-primary-content/70 hover:bg-primary-content/10 hover:text-primary-content"
                    >
                        Admin
                    </a>
                </template>
            </div>
        </div>
    </div>

    <!-- Report modal -->
    <dialog class="modal" :class="{ 'modal-open': showReportModal }">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">{{ trans('user.report') }}</h3>
            <div class="form-control mb-3">
                <label class="label">
                    <span class="label-text">{{ trans('report.reason') }}</span>
                </label>
                <select v-model="reportReason" class="select select-bordered w-full" required>
                    <option value="" disabled>—</option>
                    <option
                        v-for="r in ['inappropriate', 'implausible', 'spam', 'illegal', 'other']"
                        :key="r"
                        :value="r"
                    >
                        {{ trans(`report-reason.${r}`) }}
                    </option>
                </select>
            </div>
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text">{{ trans('report.description') }}</span>
                </label>
                <textarea
                    v-model="reportDescription"
                    class="textarea textarea-bordered w-full"
                    rows="3"
                    minlength="10"
                />
                <label class="label">
                    <span class="label-text-alt text-base-content/50">{{ trans('report.min-length') }}</span>
                </label>
            </div>
            <div class="modal-action">
                <button class="btn btn-ghost" @click="showReportModal = false">{{ trans('cancel') }}</button>
                <button
                    class="btn btn-primary"
                    :disabled="!reportReason || reportDescription.length < 10 || reportLoading"
                    @click="submitReport"
                >
                    <span v-if="reportLoading" class="loading loading-spinner loading-xs" />
                    {{ trans('report.submit') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @submit.prevent="showReportModal = false">
            <button>close</button>
        </form>
    </dialog>
</template>
