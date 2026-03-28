<script setup lang="ts">
import { computed } from 'vue';
import { UserResource } from '../../../../types/Api.gen';
import { IconHelper } from '../../../helpers/IconHelper';

const props = defineProps<{
    userData: UserResource;
}>();

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
    <div v-if="userData.bio || mergedLinks.length" class="card mb-3 shadow-sm rounded">
        <div class="card-body">
            <p v-if="userData.bio" class="text-muted fst-italic m-0">
                <i class="fa fa-quote-left me-2" />
                <!-- eslint-disable-next-line vue/no-v-html -->
                <span class="profile-bio" v-html="userData.bio" />
            </p>
            <div v-if="mergedLinks.length" class="d-flex justify-content-center flex-wrap gap-3 mt-4">
                <a
                    v-for="(link, index) in mergedLinks"
                    :key="index"
                    :href="link.url"
                    class="text-muted fs-4"
                    :aria-label="link.name"
                    target="_blank"
                    rel="me"
                >
                    <i :class="IconHelper.getLinkIcon(link.name) || 'fa-solid fa-link'" />
                </a>
            </div>
        </div>
    </div>
</template>
