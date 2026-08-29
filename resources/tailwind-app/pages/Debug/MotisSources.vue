<script setup lang="ts">
import { Info, SquareArrowOutUpRight } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import { Api, MotisSourceLicenseResource } from '../../../types/Api.gen';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });

const sources = ref<MotisSourceLicenseResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

function fetchData(): void {
    api.motisSources
        .getMotisSources()
        .then((res) => {
            sources.value = res.data.data ?? [];
        })
        .catch((e: unknown) => {
            error.value = e instanceof Error ? e.message : 'Error';
        })
        .finally(() => {
            loading.value = false;
        });
}

onMounted(fetchData);
</script>

<template>
    <AppLayout>
        <div class="flex justify-center">
            <div class="w-full lg:w-5/6">
                <h1 class="text-3xl py-3">Motis sources</h1>

                <div role="alert" class="alert my-2">
                    <Info class="inline size-6 stroke-info" />
                    <span
                        >This page is for debugging purposes only. It shows the raw transit data sources used by this
                        instance.</span
                    >
                </div>

                <div v-if="loading" class="skeleton border border-base-300 w-full h-80" />

                <div v-else-if="error" role="alert" class="alert alert-error">
                    <span>{{ error }}</span>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Source</th>
                                <th>License</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="source in sources" :key="source.name ?? '' + source.country">
                                <td>{{ source.country }}</td>
                                <td>
                                    {{ source.humanName }}
                                    <br />
                                    <a
                                        v-if="source.sourceUrl"
                                        :href="source.sourceUrl"
                                        target="_blank"
                                        rel="noopener"
                                        class="link link-hover text-xs opacity-70"
                                    >
                                        {{ source.name }}
                                    </a>
                                    <span v-else class="text-xs opacity-70">{{ source.name }}</span>
                                </td>
                                <td>
                                    <template v-if="source.manualLicense">
                                        <a
                                            v-if="source.manualLicense.licenseUrl"
                                            :href="source.manualLicense.licenseUrl"
                                            target="_blank"
                                            rel="noopener"
                                            class="link link-hover"
                                        >
                                            {{ source.manualLicense.humanName }}
                                            <SquareArrowOutUpRight class="inline size-3" />
                                        </a>
                                        <span v-else>{{ source.manualLicense.humanName }}</span>
                                    </template>
                                    <template v-else-if="source.spdx">
                                        <a
                                            v-if="source.licenseUrl"
                                            :href="source.licenseUrl"
                                            target="_blank"
                                            rel="noopener"
                                            class="link link-hover"
                                        >
                                            {{ source.spdx }}
                                            <SquareArrowOutUpRight class="inline size-3" />
                                        </a>
                                        <span v-else>{{ source.spdx }}</span>
                                    </template>
                                    <span v-else-if="source.attributionText" class="badge badge-info">Custom</span>
                                    <span v-else class="badge badge-ghost">No license information</span>
                                    <div v-if="source.attributionText" class="text-xs opacity-70 mt-1">
                                        {{ source.attributionText }}
                                    </div>
                                </td>
                                <td>
                                    <span v-if="source.forceActive" class="badge badge-success">Forced Active</span>
                                    <span v-else-if="source.active" class="badge badge-success">Active</span>
                                    <span v-else class="badge badge-ghost">Inactive</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
