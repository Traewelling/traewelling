<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { PropType, ref, watch } from 'vue';
import { StatusResource } from '../../../../types/Api.gen';

const props = defineProps({
    status: {
        type: Object as PropType<StatusResource>,
        required: true,
    },
});

const showMore = ref(false);
const enrichedBody = ref<string>('');

function showMoreButton() {
    const body = props.status.body ?? '';
    showMore.value = body.length > 0 && body.split(/\r\n|\r|\n/).length > 3;
}

function escapeHtml(s: string): string {
    return s
        .replaceAll(/&/g, '&amp;')
        .replaceAll(/</g, '&lt;')
        .replaceAll(/>/g, '&gt;')
        .replaceAll(/"/g, '&quot;')
        .replaceAll(/'/g, '&#039;');
}

/**
 * Convert byte offset to character offset in a UTF-8 string.
 * PHP stores mention positions as byte offsets (from preg_match_all),
 * but JavaScript string operations work with character offsets.
 */
function byteOffsetToCharOffset(str: string, byteOffset: number): number {
    const encoder = new TextEncoder();
    let charIndex = 0;
    let byteCount = 0;

    while (charIndex < str.length && byteCount < byteOffset) {
        const char = str[charIndex];
        const encoded = encoder.encode(char);
        byteCount += encoded.length;
        charIndex++;
    }

    return charIndex;
}

function buildBodyWithMentions(): string {
    const body = props.status.body ?? '';
    const mentions = (props.status as any).bodyMentions ?? [];
    if (!body || !Array.isArray(mentions) || mentions.length === 0) {
        return escapeHtml(body);
    }

    const sorted = [...mentions].sort((a, b) => a.position - b.position);
    let result = '';
    let cursor = 0;

    for (const m of sorted) {
        // Convert byte offsets (from PHP) to character offsets (for JavaScript)
        const byteStart = Number(m.position) || 0;
        const byteLength = Number(m.length) || 0;
        const byteEnd = byteStart + byteLength;

        const charStart = byteOffsetToCharOffset(body, byteStart);
        const charEnd = byteOffsetToCharOffset(body, byteEnd);

        result += escapeHtml(body.slice(cursor, charStart));
        const mentionText = body.slice(charStart, charEnd);
        const username = m?.user?.username ?? mentionText.replace(/^@/, '');
        const url = `/@${encodeURIComponent(username)}`;

        result += `<a href="${url}" class="mention">${escapeHtml(mentionText)}</a>`;
        cursor = charEnd;
    }

    result += escapeHtml(body.slice(cursor));
    return result;
}

watch(
    () => props.status,
    () => {
        enrichedBody.value = buildBodyWithMentions();
        showMoreButton();
    },
    {
        immediate: true,
    },
);
</script>

<template>
    <li>
        <span class="status-body" :class="{ 'line-clamp': showMore }">
            <i class="fas fa-quote-right me-1" aria-hidden="true" />
            <span v-html="enrichedBody" />

            <button v-if="showMore" class="btn btn-link p-0" aria-expanded="false" @click="showMore = !showMore">
                {{ trans('status.show_more') }}
            </button>
        </span>

        <button v-if="showMore" class="btn btn-link p-0" aria-expanded="false" @click="showMore = !showMore">
            {{ trans('status.show_more') }}
        </button>
    </li>
</template>

<style scoped>
.status-body {
    white-space: pre-wrap;
    overflow-wrap: break-word;
    word-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
    -webkit-box-orient: vertical;
    display: -webkit-box;
}

.line-clamp {
    -webkit-line-clamp: 3;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
