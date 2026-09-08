<script setup lang="ts">
import { Info, SquareArrowOutUpRight } from '@lucide/vue';
import { getActiveLanguage } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { onMounted, ref } from 'vue';
import { Api, ChangelogResource } from '../../../types/Api.gen';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });

const changelog = ref<ChangelogResource[]>([]);
const loading = ref(true);

function fetchData() {
    api.app
        .getChangelog()
        .then((response) => {
            loading.value = false;
            changelog.value = response.data.data;
        })
        .catch((error) => {
            loading.value = false;
            console.error('Error fetching changelog:', error);
        });
}

const relativeCreatedAt = (createdAt: string) => {
    const date = DateTime.fromISO(createdAt).setLocale(getActiveLanguage());

    if (date.diffNow('days').days < -1) {
        return date.toLocaleString();
    } else {
        return date.toRelative() || '';
    }
};

onMounted(() => {
    fetchData();
});
</script>

<template>
    <AppLayout>
        <div class="flex justify-center">
            <div class="w-full lg:w-2/3">
                <h1 class="text-3xl py-3">
                    {{ $t('changelog') }}

                    <a
                        class="btn btn-xs btn-outline"
                        :href="`https://github.com/Traewelling/traewelling/releases/`"
                        target="_blank"
                    >
                        {{ $t('changelog.view_on_github') }}
                        <SquareArrowOutUpRight class="inline ms-1 size-4" />
                    </a>
                </h1>
                <div role="alert" class="alert my-2">
                    <Info class="inline size-6 stroke-info" />
                    <span>{{ $t('page-only-available-in-language', { language: $t('language.en') }) }}</span>
                </div>

                <template v-if="loading">
                    <div class="skeleton border border-base-300 w-full h-80"></div>
                    <div v-for="i in 10" :key="i" class="skeleton border border-base-300 w-full h-20"></div>
                </template>

                <div
                    v-for="entry in changelog"
                    :key="entry.tagName"
                    class="collapse collapse-plus bg-base-100 border border-base-300"
                >
                    <input type="radio" name="changelog-accordion" :checked="entry === changelog[0]" />
                    <div class="collapse-title font-semibold">
                        <h2 class="text-2xl">
                            {{ entry.title }}
                            <span class="text-xs opacity-60">{{ relativeCreatedAt(entry.createdAt) }}</span>
                        </h2>
                    </div>
                    <div class="collapse-content text-sm prose">
                        <ul>
                            <li v-for="change in entry.changes" :key="change.info">
                                {{ change.emoji }} {{ change.info }}
                            </li>
                        </ul>
                        <a
                            class="btn btn-sm btn-outline"
                            :href="`https://github.com/Traewelling/traewelling/releases/tag/${entry.tagName}`"
                            target="_blank"
                        >
                            {{ $t('changelog.view_on_github') }}
                            <SquareArrowOutUpRight class="inline ms-1 size-4" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
