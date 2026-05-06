<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { CheckCircle, Copy } from 'lucide-vue-next';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { StatusResource } from '../../../types/Api.gen';
import StatusCard from '../../components/Status/StatusCard.vue';
import AppLayout from '../../layouts/AppLayout.vue';

interface DuplicateGroup {
    statuses: StatusResource[];
}

const notyf = inject('notyf') as Notyf;

const groups = ref<DuplicateGroup[]>([]);
const loading = ref(true);

async function fetchDuplicates(): Promise<void> {
    loading.value = true;
    try {
        const response = await fetch('/api/v1/statuses/duplicates', {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        groups.value = data.data ?? [];
    } catch {
        notyf.error(trans('generic.error'));
    } finally {
        loading.value = false;
    }
}

function onStatusDeleted(group: DuplicateGroup, statusId: number): void {
    group.statuses = group.statuses.filter((s) => s.id !== statusId);
    groups.value = groups.value.filter((g) => g.statuses.length > 1);
}

fetchDuplicates();
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <h1 class="text-2xl lg:text-3xl font-bold mb-1">{{ trans('checkin.duplicates.title') }}</h1>
            <p v-if="loading || groups.length > 0" class="text-base-content/60 mb-6">
                {{ trans('checkin.duplicates.description') }}
            </p>

            <template v-if="loading">
                <div v-for="n in 2" :key="n" class="card bg-base-100 mb-4">
                    <div class="card-body gap-3">
                        <div class="skeleton h-4 w-48 rounded" />
                        <div class="skeleton h-28 w-full rounded" />
                        <div class="skeleton h-28 w-full rounded" />
                    </div>
                </div>
            </template>

            <template v-else>
                <div v-if="groups.length === 0" class="card bg-base-100">
                    <div class="card-body items-center text-center gap-3 py-12">
                        <CheckCircle class="w-12 h-12 text-success" />
                        <h3 class="font-semibold text-lg">{{ trans('checkin.duplicates.none') }}</h3>
                    </div>
                </div>

                <div v-for="(group, gi) in groups" :key="gi" class="mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <Copy class="w-4 h-4 text-warning" />
                        <span class="font-semibold text-sm">
                            {{ trans('checkin.duplicates.group', { count: group.statuses.length }) }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-3">
                        <StatusCard
                            v-for="status in group.statuses"
                            :key="status.id"
                            :status="status"
                            @status-deleted="onStatusDeleted(group, status.id)"
                        />
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
