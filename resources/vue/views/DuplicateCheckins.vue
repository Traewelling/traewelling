<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { ref } from 'vue';
import { StatusResource } from '../../types/Api.gen';
import StatusCard from '../components/Status/StatusCard.vue';

interface DuplicateGroup {
    statuses: StatusResource[];
}

const notyf = new Notyf({ position: { x: 'right', y: 'bottom' } });

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
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <h1 class="fs-4 mb-1">{{ trans('checkin.duplicates.title') }}</h1>
            <p v-if="loading || groups.length > 0" class="text-muted mb-4">{{ trans('checkin.duplicates.description') }}</p>

            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">{{ trans('menu.loading') }}</span>
                </div>
            </div>

            <div v-else-if="groups.length === 0" class="alert alert-success">
                <i class="fa fa-check-circle me-2"></i>{{ trans('checkin.duplicates.none') }}
            </div>

            <div v-else>
                <div v-for="(group, gi) in groups" :key="gi" class="mb-2">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-clone text-warning" />
                        <span class="small fw-semibold text-muted">
                            {{ trans('checkin.duplicates.group', { count: group.statuses.length }) }}
                        </span>
                        <hr class="flex-grow-1 my-0" />
                    </div>
                    <StatusCard
                        v-for="status in group.statuses"
                        :key="status.id"
                        :status="status"
                        @status-deleted="onStatusDeleted(group, status.id)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
