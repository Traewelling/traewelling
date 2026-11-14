<script setup lang="ts">
import { Globe } from '@lucide/vue';
import { loadLanguageAsync, trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject } from 'vue';
import { Api } from '../../../types/Api.gen';
import { useConfigurationStore } from '../../../vue/stores/configuration';
import { useUserStore } from '../../../vue/stores/user';

const api = new Api({ baseUrl: window.location.origin + '/api' });
const config = useConfigurationStore();
const user = useUserStore();
const notyf = inject('notyf') as Notyf;
const reloadOnLanguageChange = inject('reloadOnLanguageChange', false);
config.fetchData();

async function selectLanguage(code: string): Promise<void> {
    (document.activeElement as HTMLElement | null)?.blur();

    if (reloadOnLanguageChange) {
        const url = new URL(window.location.href);
        url.searchParams.set('language', code);
        window.location.href = url.toString();
        return;
    }

    if (user.isAuthenticated) {
        try {
            await api.settings.updateProfileSettings({ language: code });
        } catch {
            notyf.error(trans('messages.exception.general'));
            return;
        }
    }
    await loadLanguageAsync(code);
    document.documentElement.lang = code.replace('_', '-');
}
</script>

<template>
    <div class="dropdown dropdown-top w-full">
        <div tabindex="0" role="button" class="btn w-full btn-secondary">
            <Globe class="inline-block w-4 h-4 mr-2" />
            {{ trans('settings.language.set') }}
        </div>
        <ul
            tabindex="-1"
            class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm text-base-content"
        >
            <li v-for="lang in config.languages" :key="lang.code">
                <button type="button" @click="selectLanguage(lang.code)">
                    {{ lang.name }}
                </button>
            </li>
        </ul>
    </div>
</template>
