<script setup lang="ts">
import { Globe } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { useConfigurationStore } from '../../../vue/stores/configuration';

const config = useConfigurationStore();
config.fetchData();

const selectLanguageUrl = (langCode: string): string => {
    const url = new URL(window.location.href);
    url.searchParams.set('language', langCode);
    return url.toString();
};
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
            <li>
                <a
                    v-for="lang in config.languages"
                    :key="lang.code"
                    class="dropdown-item"
                    :href="selectLanguageUrl(lang.code)"
                >
                    {{ lang.name }}
                </a>
            </li>
        </ul>
    </div>
</template>
