<script setup lang="ts">
import GitHub from '../Icons/GitHub.vue';

defineProps<{
    label: string;
    repoUrl: string;
    repoLabel: string;
    licenseLabel: string;
}>();

const command = 'git clone https://github.com/traewelling/traewelling.git';
</script>

<template>
    <div :aria-label="label" role="group" class="card bg-base-100 shadow-sm">
        <div class="card-body p-4 gap-3">
            <pre class="rounded-box bg-base-300 p-3 text-xs overflow-x-auto"><code>{{ command }}</code></pre>

            <p class="text-sm text-base-content/60">{{ licenseLabel }}</p>

            <a
                :href="repoUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-outline btn-sm self-start gap-2"
            >
                <span class="fill-current stroke-none inline-flex size-4" aria-hidden="true">
                    <GitHub class="size-4" />
                </span>
                {{ repoLabel }}
            </a>
        </div>
    </div>
</template>
