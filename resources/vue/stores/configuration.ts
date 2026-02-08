import { defineStore } from 'pinia';
import { Api, ConfigurationFeatureEnum, ConfigurationInformation, Feature, Language } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

export const useConfigurationStore = defineStore('apiConfiguration', {
    persist: true,
    state: () => ({
        configuration: null as ConfigurationInformation | null,
        loading: false,
        error: null as unknown | null,
        refreshed: '2021-08-01T12:00:00Z',
    }),
    getters: {
        appName: (state): string => {
            return state.configuration?.appName || 'App';
        },
        appVersion: (state): string => {
            return state.configuration?.version || 'unknown';
        },
        languages: (state): Language[] => {
            return state.configuration?.languages || [];
        },
        isFeatureEnabled: (state) => {
            return (feature: ConfigurationFeatureEnum): boolean => {
                return (
                    state.configuration?.features?.some((f: Feature) => {
                        return (f.name as ConfigurationFeatureEnum) === feature && f.enabled;
                    }) || false
                );
            };
        },
    },
    actions: {
        async fetchData(force: boolean = false): Promise<void> {
            // Fetch Data every 5 Minutes
            if (
                force ||
                (this.refreshed && new Date().getTime() - new Date(this.refreshed).getTime() > 60 * 5 * 1000)
            ) {
                this.loading = true;
                api.app
                    .getConfigurationInfo()
                    .then((response) => {
                        this.configuration = response.data || null;

                        this.loading = false;
                        this.refreshed = new Date().toString();
                    })
                    .catch((error) => {
                        this.error = error;

                        this.loading = false;
                        this.refreshed = new Date().toString();
                    });
            }
        },
    },
});
