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
    <div class="prose max-w-full" v-html="html" />
</template>
