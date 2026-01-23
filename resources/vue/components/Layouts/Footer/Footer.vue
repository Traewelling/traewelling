<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { useConfigurationStore } from '../../../stores/configuration';

const config = useConfigurationStore();
config.fetchData();

const selectLanguageUrl = (langCode: string): string => {
    const url = new URL(window.location.href);
    url.searchParams.set('language', langCode);
    return url.toString();
};
</script>

<template>
    <footer class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-6 col-md-2 mb-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a href="/events" class="nav-link p-0 text-body-secondary">
                                {{ trans('events') }}
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a
                                href="https://help.traewelling.de/faq/"
                                target="_blank"
                                class="nav-link p-0 text-body-secondary"
                            >
                                {{ trans('menu.about') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-6 col-md-2 mb-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a href="/legal/privacy-policy" class="nav-link p-0 text-body-secondary">
                                {{ trans('menu.privacy') }}
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="/legal" class="nav-link p-0 text-body-secondary">
                                {{ trans('menu.legal-notice') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-6 col-md-2 mb-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a
                                href="https://blog.traewelling.de"
                                target="blog"
                                class="nav-link p-0 text-body-secondary"
                            >
                                {{ trans('menu.blog') }}
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a
                                href="https://chaos.social/@traewelling"
                                target="_blank"
                                class="nav-link p-0 text-body-secondary"
                            >
                                Mastodon
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-auto ms-md-auto mb-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <div class="btn-group dropup w-100">
                                <button
                                    type="button"
                                    class="btn btn-primary btn-block dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    <i class="fas fa-globe-europe"></i> {{ trans('settings.language.set') }}
                                </button>
                                <div class="dropdown-menu">
                                    <a
                                        v-for="lang in config.languages"
                                        :key="lang.code"
                                        class="dropdown-item"
                                        :href="selectLanguageUrl(lang.code)"
                                    >
                                        {{ lang.name }}
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item mb-2">
                            <div class="btn-group dropup w-100">
                                <button
                                    type="button"
                                    class="btn btn-primary btn-block dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    <i class="fas fa-circle-half-stroke"></i>
                                    {{ trans('settings.colorscheme.set') }}
                                </button>
                                <div class="dropdown-menu">
                                    <div class="dropdown-item" id="colorModeToggleLight">
                                        <i class="fas fa-sun"></i>
                                        {{ trans('settings.colorscheme.light') }}
                                    </div>
                                    <div class="dropdown-item" id="colorModeToggleDark">
                                        <i class="fas fa-moon"></i>
                                        {{ trans('settings.colorscheme.dark') }}
                                    </div>
                                    <div class="dropdown-item" id="colorModeToggleAuto">
                                        <i class="fas fa-circle-half-stroke"></i>
                                        {{ trans('settings.colorscheme.auto') }}
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row justify-content-between py-4 my-4 border-top">
                <p class="mb-0">&copy; {{ DateTime.now().toFormat('Y') }} Tr&auml;welling</p>
                <p class="mb-0" v-html="trans('menu.developed')"></p>
                <p class="mb-0 text-muted small">
                    Version
                    <a href="/changelog">
                        {{ config.appVersion }}
                    </a>
                </p>
            </div>
        </div>
    </footer>
</template>
