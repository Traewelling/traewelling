<script setup lang="ts">
import { Calendar, Clock, HashIcon, LinkIcon, Route, ShieldCogCorner, SquareArrowOutUpRight } from '@lucide/vue';
import { currentLocale } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { EventResource } from '../../../../types/Api.gen';
import { useUserStore } from '../../../../vue/stores/user';
import DistanceSpan from '../../../components/Stats/DistanceSpan.vue';
import DurationSpan from '../../../components/Stats/DurationSpan.vue';

const props = defineProps<{
    event: EventResource;
}>();

const user = useUserStore();

const duration = computed(() => {
    const locale = currentLocale.value.startsWith('de') ? 'de' : currentLocale.value;
    const begin = new Date(props.event.begin);
    const end = new Date(props.event.end);
    const options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'long', year: 'numeric' };
    if (begin.getFullYear() !== end.getFullYear()) {
        options.year = 'numeric';
    }
    if (begin.getMonth() !== end.getMonth() || begin.getDate() !== end.getDate()) {
        return `${begin.toLocaleDateString(locale, options)} - ${end.toLocaleDateString(locale, options)}`;
    } else {
        return begin.toLocaleDateString(locale, options);
    }
});
</script>

<template>
    <h3 class="mb-4 text-lg font-bold">
        {{ event.name }}
        <a v-if="user.isAdmin" :href="`/admin/events/${event.id}/edit`" class="btn btn-ghost btn-sm">
            <ShieldCogCorner class="inline-block size-3.5">
                <title>{{ $t('menu.backend') }}</title>
            </ShieldCogCorner>
        </a>
    </h3>
    <p>
        <Calendar class="inline-block size-5 me-1">
            <title>
                {{ $t('events.duration') }}
            </title>
        </Calendar>
        {{ duration }}
    </p>
    <p v-if="event.hashtag">
        <HashIcon class="inline-block size-5 me-2">
            <title>#</title>
        </HashIcon>
        {{ event.hashtag }}
    </p>
    <p v-if="event.url">
        <LinkIcon class="inline-block size-5 me-2">
            <title>{{ $t('events.url') }}</title>
        </LinkIcon>
        <a :href="event.url" target="_blank" class="link link-primary">
            {{ event.url }}
            <SquareArrowOutUpRight class="inline-block size-5" />
        </a>
    </p>
    <p>
        <Route class="inline-block size-5 me-2" />
        <DistanceSpan :distance="event.totalDistance ?? 0" />
    </p>
    <p>
        <Clock class="inline-block size-5 me-2" />
        <DurationSpan :duration="event.totalDuration ?? 0" />
    </p>
    <div v-if="event.host || event.station" class="mt-3">
        <p v-if="event.host">{{ $t('events.host') }}: {{ event.host }}</p>
        <p v-if="event.station">
            {{ $t('events.closestStation') }}:
            <a class="link" :href="`/stationboard?stationId=${event.station.id}&stationName=${event.station.name}`">{{
                event.station.name
            }}</a>
        </p>
    </div>
</template>
