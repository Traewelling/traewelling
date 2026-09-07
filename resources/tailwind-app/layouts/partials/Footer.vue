<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { useConfigurationStore } from '../../../vue/stores/configuration';

const config = useConfigurationStore();
config.fetchData();

const elsewhereLinks = [
    { name: 'Blog', url: 'https://blog.traewelling.de' },
    { name: 'Mastodon', url: 'https://chaos.social/@traewelling' },
    { name: 'Matrix', url: 'https://matrix.to/#/#space:traewelling.org' },
    { name: 'GitHub', url: 'https://github.com/traewelling/traewelling' },
];
</script>

<template>
    <footer class="footer max-md:footer-center footer-horizontal bg-primary text-white p-10">
        <aside>
            <div class="flex items-center space-x-2 mb-4">
                <img src="/images/icons/logo.svg" class="h-12 w-12" alt="Träwelling Logo" />
                <h1 class="text-2xl text-bold">#Träwelling</h1>
            </div>
            <p>
                {{ trans('footer.developed') }}
                <br />
                <a href="https://github.com/Traewelling/traewelling" target="_blank" class="link link-hover">
                    {{ trans('footer.sourcecode') }}
                </a>
                {{ trans('footer.licensed-under') }}
                <a href="https://www.gnu.org/licenses/agpl-3.0.html" target="_blank" class="link link-hover">AGPLv3</a>.
                <br />

                {{ trans('welcome.footer.version') }}
                <router-link :to="{ name: 'changelog' }" class="link link-hover">
                    {{ config.appVersion }}
                </router-link>
            </p>
        </aside>
        <!-- mobile footer -->
        <nav class="md:hidden">
            <div class="flex flex-wrap justify-center gap-2">
                <router-link :to="{ name: 'event-list' }" class="link link-hover">{{ trans('events') }}</router-link>
                <a href="https://help.traewelling.de/features/" target="_blank" class="link link-hover">
                    {{ trans('menu.about') }}
                </a>
                <a
                    v-for="link in elsewhereLinks"
                    :key="link.name"
                    target="_blank"
                    class="link link-hover"
                    :href="link.url"
                >
                    {{ link.name }}
                </a>
                <a href="/legal/privacy-policy" class="link link-hover">
                    {{ trans('menu.privacy') }}
                </a>
                <a href="/legal" class="link link-hover">
                    {{ trans('menu.legal-notice') }}
                </a>
            </div>
        </nav>
        <!-- /mobile footer -->
        <nav class="max-sm:hidden">
            <h6 class="footer-title sm:hidden">{{ trans('footer.services') }}</h6>
            <router-link :to="{ name: 'event-list' }" class="link link-hover">{{ trans('events') }}</router-link>
            <a href="https://help.traewelling.de/features/" target="_blank" class="link link-hover">
                {{ trans('menu.about') }}
            </a>
        </nav>
        <nav class="max-sm:hidden">
            <h6 class="footer-title">{{ trans('footer.elsewhere') }}</h6>
            <a v-for="link in elsewhereLinks" :key="link.name" target="_blank" class="link link-hover" :href="link.url">
                {{ link.name }}
            </a>
        </nav>
        <nav class="max-sm:hidden">
            <h6 class="footer-title">{{ trans('footer.legal') }}</h6>
            <a href="/legal/privacy-policy" class="link link-hover">
                {{ trans('menu.privacy') }}
            </a>
            <a href="/legal" class="link link-hover">
                {{ trans('menu.legal-notice') }}
            </a>
        </nav>
        <nav>
            <DarkModeSelector />
            <LanguageSelector />
        </nav>
    </footer>
</template>
