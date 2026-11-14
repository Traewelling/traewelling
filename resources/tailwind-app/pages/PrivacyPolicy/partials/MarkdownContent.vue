<script setup lang="ts">
import DOMPurify from 'dompurify';
import { marked } from 'marked';
import { computed } from 'vue';

const props = defineProps<{
    markdown: string;
}>();

// v-html is intentional: content comes from our own database and is DOMPurify-sanitized.
const html = computed(() => DOMPurify.sanitize(marked.parse(props.markdown) as string));
</script>

<template>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div class="prose prose-sm max-w-full prose-h1:text-xl prose-h2:text-lg prose-h3:text-base" v-html="html" />
</template>
