<script setup lang="ts">
import { CheckCircle, Download, ShieldCheck } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { computed, inject, ref } from 'vue';
import { Api } from '../../../../types/Api.gen';
import { useConfigurationStore } from '../../../../vue/stores/configuration';
import { useUserStore } from '../../../../vue/stores/user';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;
const loading = ref(false);
const requested = ref(false);
const user = useUserStore();
const config = useConfigurationStore();

async function requestExport() {
    loading.value = true;
    try {
        await api.export.requestGdprExport();
        requested.value = true;
        user.fetchSettings(true);
    } catch (err: unknown) {
        const e = err as { error?: { error?: string } } | undefined;
        notyf.error(e?.error?.error ?? trans('export.error.gdpr'));
    } finally {
        loading.value = false;
    }
}

const canRequestExport = computed(() => {
    if (user.user?.recentGdprExport === null || user.user?.recentGdprExport === undefined) {
        return true;
    }

    const cooldown = config.configuration.gdprExportCooldown ?? 30;
    return DateTime.fromISO(user.user.recentGdprExport).plus({ days: cooldown }) < DateTime.now();
});
</script>

<template>
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body gap-4">
            <h2 class="card-title text-base">
                <ShieldCheck class="w-5 h-5" />
                {{ trans('export.gdpr') }}
            </h2>

            <p class="text-sm text-base-content/70">{{ trans('export.gdpr.description') }}</p>

            <div v-if="requested" role="alert" class="alert alert-success py-2">
                <CheckCircle class="w-4 h-4 shrink-0" />
                <span class="text-sm">{{ trans('notifications.personalDataExported.lead') }}</span>
            </div>

            <div v-else class="card-actions justify-end">
                <button class="btn btn-primary btn-sm" :disabled="loading || !canRequestExport" @click="requestExport">
                    <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                    <Download v-else class="w-4 h-4" />
                    {{ trans('export.request') }}
                </button>
            </div>
        </div>
    </div>
</template>
