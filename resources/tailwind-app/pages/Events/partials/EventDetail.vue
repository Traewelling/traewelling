<script setup lang="ts">
import { currentLocale } from 'laravel-vue-i18n';
import { Calendar, HashIcon, LinkIcon, SquareArrowOutUpRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { EventResource } from '../../../../types/Api.gen';

const props = defineProps<{
    event: EventResource;
}>();

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
    <h3 class="mb-4 text-lg font-bold">{{ event.name }}</h3>
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
