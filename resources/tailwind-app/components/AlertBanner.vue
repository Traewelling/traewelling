<script setup lang="ts">
import { AlertTriangle, CheckCircle, Info, XCircle } from '@lucide/vue';
import { getActiveLanguage } from 'laravel-vue-i18n';
import { onMounted, ref } from 'vue';
import { AlertResource, AlertTranslationResource, Api } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const alerts = ref<AlertResource[]>([]);

onMounted(async () => {
    try {
        const res = await api.alerts.getAlerts();
        const json = await res.json();
        alerts.value = json.data ?? [];
    } catch {
        // alerts are best-effort
    }
});

function translation(alert: AlertResource): AlertTranslationResource | null {
    const locale = getActiveLanguage();
    return (
        alert.translations.find((t) => locale.startsWith(t.locale)) ??
        alert.translations.find((t) => t.locale === 'en') ??
        null
    );
}

function alertClass(type: AlertResource['type']): string {
    const map: Record<AlertResource['type'], string> = {
        info: 'alert-info',
        success: 'alert-success',
        warning: 'alert-warning',
        danger: 'alert-error',
    };
    return map[type] ?? '';
}
</script>

<template>
    <template v-for="alert in alerts" :key="alert.id">
        <div class="alert mb-3" :class="alertClass(alert.type)" role="alert">
            <Info v-if="alert.type === 'info'" class="w-5 h-5 shrink-0" />
            <CheckCircle v-else-if="alert.type === 'success'" class="w-5 h-5 shrink-0" />
            <AlertTriangle v-else-if="alert.type === 'warning'" class="w-5 h-5 shrink-0" />
            <XCircle v-else-if="alert.type === 'danger'" class="w-5 h-5 shrink-0" />
            <div>
                <p v-if="translation(alert)?.title" class="font-semibold">{{ translation(alert)?.title }}</p>
                <p v-if="translation(alert)?.content" class="text-sm whitespace-pre-wrap">
                    {{ translation(alert)?.content }}
                </p>
                <a
                    v-if="translation(alert)?.url || alert.url"
                    :href="translation(alert)?.url || alert.url || ''"
                    target="_blank"
                    class="link text-sm mt-1 inline-block"
                >
                    {{ translation(alert)?.url || alert.url }}
                </a>
            </div>
        </div>
    </template>
</template>
