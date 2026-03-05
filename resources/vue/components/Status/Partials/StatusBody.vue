<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { PropType, ref, watch } from 'vue';
import { MentionDto, StatusResource, UserResource } from '../../../../types/Api.gen';

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

function buildBodyWithMentions(): string {
    const body = props.status.body ?? '';
    const mentions: MentionDto[] = props.status.bodyMentions ?? [];
    if (!body) {
        return escapeHtml(body);
    }

    // Build a map of lowercase username -> user object from bodyMentions.
    // We ignore backend-provided position/length values entirely, because PHP counts
    // byte offsets while JS counts UTF-16 code units, causing mismatches with emojis.
    // Instead, we search for @username patterns directly in the body text.
    const userMap = new Map<string, UserResource>();
    for (const m of mentions) {
        if (m.user?.username) {
            userMap.set(m.user.username.toLowerCase(), m.user);
        }
    }

    if (userMap.size === 0) {
        return escapeHtml(body);
    }

    const mentionRegex = /@([a-zA-Z0-9_]+)/g;
    let result = '';
    let lastIndex = 0;
    let match: RegExpExecArray | null;

    while ((match = mentionRegex.exec(body)) !== null) {
        const user = userMap.get(match[1].toLowerCase());
        if (!user) continue;

        result += escapeHtml(body.slice(lastIndex, match.index));
        result += `<a href="/@${encodeURIComponent(user.username)}" class="mention">${escapeHtml(match[0])}</a>`;
        lastIndex = match.index + match[0].length;
    }

    result += escapeHtml(body.slice(lastIndex));
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
            <!-- eslint-disable-next-line vue/no-v-html -->
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
