<script setup lang="ts">
import { computed } from 'vue';
import { UserResource } from '../../../../types/Api.gen';
import { IconHelper } from '../../../helpers/IconHelper';

const props = defineProps({
    userData: {
        type: Object as () => UserResource,
        required: true,
    },
});

const mergedLinks = computed(() => {
    const links = [...(props.userData.profileLinks ?? [])];
    const hasMastodon = links.some((l) => (l.name || '').toUpperCase() === 'MASTODON');
    if (props.userData.mastodonUrl && !hasMastodon) {
        links.push({ name: 'mastodon', url: props.userData.mastodonUrl });
    }
    return links;
});
</script>

<template>
    <div v-if="userData.bio || mergedLinks.length" class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <p v-if="userData.bio" class="text-sm text-base-content/70 italic whitespace-pre-wrap">
                {{ userData.bio }}
            </p>
            <div v-if="mergedLinks.length" class="flex flex-wrap gap-3 mt-2">
                <a
                    v-for="(link, i) in mergedLinks"
                    :key="i"
                    :href="link.url"
                    target="_blank"
                    rel="me noopener"
                    :aria-label="link.name ?? ''"
                    class="flex items-center gap-1 text-sm text-base-content/50 hover:text-primary transition-colors tooltip"
                    :data-tip="link.name"
                >
                    <component
                        :is="IconHelper.getLinkIcon(link.name)"
                        class="w-4 h-4 stroke-base-content"
                        :class="link.name === 'website' ? '' : 'fill-base-content '"
                    />
                </a>
            </div>
        </div>
    </div>
</template>
