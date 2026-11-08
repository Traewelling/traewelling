<script setup lang="ts">
import { computed } from 'vue';
import type { ReportResource } from '../../../types/Api.gen';

const props = defineProps<{ report: ReportResource; compact?: boolean }>();

const subjectUrl = computed((): string | null => {
    switch (props.report.subject_type) {
        case 'Status':
            return `/admin/statuses/${props.report.subject_id}`;
        case 'User':
            return `/admin/users/${props.report.subject_id}`;
        case 'Event':
            return `/admin/events/${props.report.subject_id}/edit`;
        case 'Trip':
            return `/admin/trips/${props.report.subject_id}`;
        default:
            return null;
    }
});
</script>

<template>
    <div class="text-sm space-y-0.5">
        <div class="flex items-center gap-1">
            <a v-if="subjectUrl" :href="subjectUrl" class="link link-hover" @click.stop>
                {{ report.subject_type }} <span class="font-mono text-base-content/60">#{{ report.subject_id }}</span>
            </a>
            <span v-else>{{ report.subject_type }} #{{ report.subject_id }}</span>
            <span v-if="report.subject?.manual_trip" class="badge badge-xs badge-warning">manual trip</span>
            <span
                v-if="report.other_unresolved_reports"
                class="badge badge-xs badge-error"
                :title="`${report.other_unresolved_reports} other open or waiting reports on this subject`"
            >
                +{{ report.other_unresolved_reports }}
            </span>
        </div>
        <template v-if="report.subject">
            <a
                v-if="report.subject.user"
                :href="`/admin/users/${report.subject.user.id}`"
                class="link link-hover text-xs block"
                @click.stop
            >
                @{{ report.subject.user.username }}
            </a>
            <div v-if="report.subject.name" class="text-xs">{{ report.subject.name }}</div>
            <div v-if="report.subject.origin || report.subject.line_name" class="text-xs text-base-content/70">
                <span v-if="report.subject.line_name" class="font-mono">{{ report.subject.line_name }}</span>
                {{ report.subject.origin }} → {{ report.subject.destination }}
            </div>
            <div
                v-if="report.subject.body"
                class="text-xs italic text-base-content/60"
                :class="compact ? 'max-w-64 truncate' : 'whitespace-pre-line'"
            >
                {{ report.subject.body }}
            </div>
        </template>
        <div v-else-if="report.subject === null" class="text-xs text-base-content/40">deleted</div>
    </div>
</template>
