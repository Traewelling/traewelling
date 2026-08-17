<script setup lang="ts">
import { Terminal } from '@lucide/vue';

defineProps<{
    label: string;
    docsUrl: string;
    docsLabel: string;
}>();

const request = 'curl https://traewelling.de/api/v1/status/12345';
const response = `{
  "data": {
    "id": 12345,
    "visibility": 0,
    "likes": 23,
    "checkin": {
      "lineName": "ICE 74",
      "category": "nationalExpress",
      "distance": 640000,
      "duration": 330,
      "origin": { "name": "Karlsruhe Hbf" },
      "destination": { "name": "Hamburg Hbf" }
    }
  }
}`;
</script>

<template>
    <div :aria-label="label" role="group" class="card bg-base-100 shadow-sm">
        <div class="card-body p-4 gap-3">
            <pre
                class="rounded-box bg-base-300 p-3 text-xs overflow-x-auto"
            ><code class="flex items-center gap-2"><Terminal class="size-4 shrink-0 text-primary" />{{ request }}</code></pre>
            <pre class="rounded-box bg-base-300 p-3 text-xs overflow-x-auto"><code>{{ response }}</code></pre>
            <a :href="docsUrl" class="link link-primary text-sm self-start">{{ docsLabel }}</a>
        </div>
    </div>
</template>
