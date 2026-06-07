<script setup lang="ts">
import { AtSign, Calendar, Download, Heart, Mail, MailOpen, Train, TriangleAlert, UserPlus, Users } from '@lucide/vue';
import { computed, FunctionalComponent } from 'vue';
import { useRouter } from 'vue-router';
import { Notification } from '../../../../types/Api.gen';

const props = defineProps<{
    notification: Notification;
}>();

const emit = defineEmits<{
    (e: 'toggle-read'): void;
}>();

const iconMap: Record<string, FunctionalComponent> = {
    EventSuggestionProcessed: Calendar,
    FollowRequestApproved: UserPlus,
    FollowRequestIssued: UserPlus,
    MastodonNotSent: TriangleAlert,
    InvalidMastodonServer: TriangleAlert,
    StatusLiked: Heart,
    UserFollowed: Users,
    UserJoinedConnection: Train,
    UserMentioned: AtSign,
    PersonalDataExportedNotification: Download,
};

const router = useRouter();

const icon = computed(() => iconMap[props.notification.type] ?? Mail);

const isWarning = computed(() => ['MastodonNotSent', 'InvalidMastodonServer'].includes(props.notification.type));

const isUnread = computed(() => !props.notification.readAt);

const link = computed(() => props.notification.link || undefined);

const routerPath = computed(() => {
    if (!link.value) return null;
    try {
        const url = new URL(link.value);
        if (url.origin !== window.location.origin) return null;
        const path = url.pathname + url.search + url.hash;
        if (router.resolve(path).name === 'not-found') return null;
        return path;
    } catch {
        return null;
    }
});
</script>

<template>
    <li
        class="list-row items-center transition-colors"
        :class="{
            'bg-warning/10': isUnread && isWarning,
            'bg-primary/10': isUnread && !isWarning,
        }"
    >
        <component
            :is="routerPath ? 'router-link' : 'a'"
            v-bind="routerPath ? { to: routerPath } : { href: link }"
            class="flex items-center gap-4 flex-1 min-w-0 list-col-grow"
        >
            <div
                class="flex items-center justify-center size-10 rounded-full shrink-0"
                :class="isWarning ? 'bg-warning/20 text-warning' : 'bg-primary/20 text-primary'"
            >
                <component :is="icon" class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <!-- eslint-disable-next-line vue/no-v-html -->
                <p class="font-semibold mb-0 text-sm" v-html="notification.leadFormatted" />
                <!-- eslint-disable-next-line vue/no-v-html -->
                <p class="text-sm opacity-75 mb-0" v-html="notification.noticeFormatted" />
                <p class="text-xs opacity-50 mb-0 mt-1">{{ notification.createdAtForHumans }}</p>
            </div>
        </component>
        <button class="btn btn-ghost btn-sm" @click.prevent="emit('toggle-read')">
            <MailOpen v-if="notification.readAt" class="size-4 opacity-50" />
            <Mail v-else class="size-4" />
        </button>
    </li>
</template>
