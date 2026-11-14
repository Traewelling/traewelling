import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import {
    Api,
    type ConfigurationFeatureEnum,
    type ConfigurationInformation,
    type Feature,
    type Language,
    type LegalNotice,
} from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api' });

export const useConfigurationStore = defineStore(
    'apiConfiguration',
    () => {
        const configuration = ref<ConfigurationInformation>({
            appName: 'Träwelling',
            appDebug: false,
            appUrl: '',
            version: 'unknown',
            features: [],
            languages: [],
            gdprExportCooldown: 0,
            legalNotice: { name: null, address1: null, address2: null, email: null, phone: null },
        });
        const loading = ref<boolean>(false);
        const error = ref<unknown | null>(null);
        const refreshed = ref<string>('2021-08-01T12:00:00Z');

        const appName = computed<string>(() => configuration.value.appName);
        const appVersion = computed<string>(() => configuration.value.version);
        const languages = computed<Language[]>(() => configuration.value.languages);
        const legalNotice = computed<LegalNotice | null>(() => configuration.value.legalNotice ?? null);

        function isFeatureEnabled(feature: ConfigurationFeatureEnum): boolean {
            return (
                configuration.value?.features?.some((f: Feature) => {
                    return (f.name as ConfigurationFeatureEnum) === feature && f.enabled;
                }) || false
            );
        }

        async function fetchData(force: boolean = false): Promise<void> {
            // Fetch Data every 5 Minutes
            if (
                force ||
                (refreshed.value && new Date().getTime() - new Date(refreshed.value).getTime() > 60 * 5 * 1000)
            ) {
                loading.value = true;
                api.app
                    .getConfigurationInfo()
                    .then((response) => {
                        configuration.value = response.data || null;

                        loading.value = false;
                        refreshed.value = new Date().toString();
                    })
                    .catch((err: unknown) => {
                        error.value = err;

                        loading.value = false;
                        refreshed.value = new Date().toString();
                    });
            }
        }

        return {
            configuration,
            loading,
            error,
            refreshed,
            appName,
            appVersion,
            languages,
            legalNotice,
            isFeatureEnabled,
            fetchData,
        };
    },
    { persist: true },
);
