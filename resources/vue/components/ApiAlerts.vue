<script setup lang="ts">
import { getActiveLanguage } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { AlertResource, AlertTranslationResource, Api } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api' });
const alerts = ref([] as AlertResource[]);
api.alerts.getAlerts().then((data) => {
    data.json().then((data) => {
        alerts.value = data.data;
    });
});

const getTranslation = (alert: AlertResource): AlertTranslationResource | null => {
    // get i18n locale
    const i18nLocale = getActiveLanguage();
    let match: AlertTranslationResource | null = null;

    alert.translations?.forEach((translation) => {
        if (i18nLocale.startsWith(translation.locale)) {
            match = translation;
        }
    });
    if (match) {
        return match;
    }

    alert.translations?.forEach((translation) => {
        if (translation.locale === 'en') {
            match = translation;
        }
    });

    return match;
};

const getTitle = (alert: AlertResource): string => {
    const trans = getTranslation(alert);
    return trans?.title || '';
};

const getContent = (alert: AlertResource): string => {
    const trans = getTranslation(alert);
    return trans?.content || '';
};

const getUrl = (alert: AlertResource): string => {
    const url = alert.url;
    const trans = getTranslation(alert);
    return trans?.url || url || '';
};

const getIcon = (alert: AlertResource): string => {
    switch (alert.type) {
        case 'info':
            return 'fa-solid fa-circle-exclamation';
        case 'success':
            return 'fa-solid fa-circle-check';
        case 'warning':
            return 'fa-solid fa-triangle-exclamation';
        case 'danger':
            return 'fa-solid fa-circle-xmark';
        default:
            return '';
    }
};
</script>

<template>
    <div v-for="alert in alerts" :key="alert.id" class="alert" role="alert" :class="`alert-${alert.type}`">
        <h4 class="alert-heading">
            <i :class="getIcon(alert)" aria-hidden="true" />
            {{ getTitle(alert) }}
        </h4>
        <div class="alert-body">
            <pre class="alert-pre">{{ getContent(alert) }}</pre>
            <p v-if="getUrl(alert)">
                <a :href="getUrl(alert)" target="_blank" class="alert-link">
                    {{ getUrl(alert) }}
                </a>
            </p>
        </div>
    </div>
</template>

<style scoped>
.alert-body {
    min-width: 0;
    overflow: hidden;
}

.alert-pre {
    white-space: pre-wrap;
    word-break: break-word;
    overflow-wrap: anywhere;
    font-family: var(--bs-body-font-family), sans-serif;
}

.alert-link {
    word-break: break-all;
    overflow-wrap: anywhere;
}
</style>
