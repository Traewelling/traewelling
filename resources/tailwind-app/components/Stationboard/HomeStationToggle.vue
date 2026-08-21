<script setup lang="ts">
import { House } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, inject, ref } from 'vue';
import { useUserStore } from '../../../vue/stores/user';

const props = defineProps<{
    stationUuid: string;
    stationName?: string;
}>();

const notyf = inject('notyf') as Notyf;
const userStore = useUserStore();

const saving = ref(false);

const isHome = computed<boolean>(() => userStore.getHome?.uuid === props.stationUuid);

const label = computed<string>(() =>
    isHome.value ? trans('user.home-station.unset') : trans('user.home-station.set'),
);

async function toggle(): Promise<void> {
    if (saving.value) return;
    saving.value = true;
    try {
        if (isHome.value) {
            await userStore.deleteHome();
            notyf.success(trans('user.home-station.removed'));
        } else {
            await userStore.setHome(props.stationUuid);
            notyf.success(trans('user.home-set', { Station: props.stationName ?? '' }));
        }
    } catch {
        notyf.error(trans('action.error') + ' (' + label.value + ')');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <button
        class="btn btn-ghost btn-sm btn-square shrink-0"
        :disabled="saving"
        :title="label"
        :aria-label="label"
        :aria-pressed="isHome"
        @click="toggle"
    >
        <span v-if="saving" class="loading loading-spinner loading-xs" />
        <House v-else class="size-5" :class="isHome ? 'text-primary' : 'opacity-40'" />
    </button>
</template>
