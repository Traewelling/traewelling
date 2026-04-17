<script setup lang="ts">
import { getActiveLanguage } from 'laravel-vue-i18n';
import { ArrowLeft, ArrowRight, Medal } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { Api, LeaderboardUserResource } from '../../../types/Api.gen';
import Loading from '../../components/Loading.vue';
import AppLayout from '../../layouts/AppLayout.vue';
import LeaderboardEntry from './partials/LeaderboardEntry.vue';

const selectedData = ref<LeaderboardUserResource[]>([]);
const focusData = ref<'points' | 'distance' | 'duration' | 'speed'>('points');
const loading = ref(true);

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const route = useRoute();

function fetchData() {
    loading.value = true;
    const month = route.params.month as string;
    api.leaderboard
        .getMonthlyLeaderboard(month)
        .then((data) => {
            data.json().then((data) => {
                selectedData.value = data.data;
                loading.value = false;
            });
        })
        .catch(() => {
            loading.value = false;
        });
}

const currentMonth = () => {
    const month = route.params.month as string;
    return DateTime.fromFormat(month, 'yyyy-MM');
};

const monthName = (month: DateTime) => {
    return month.setLocale(getActiveLanguage()).toLocaleString({ month: 'long', year: 'numeric' });
};

const previousMonth = () => {
    return currentMonth().minus({ months: 1 });
};

const nextMonth = () => {
    if (currentMonth().plus({ months: 1 }) > DateTime.now()) {
        return null;
    }

    return currentMonth().plus({ months: 1 });
};

onMounted(fetchData);
watch(
    () => route.params.month,
    () => {
        fetchData();
    },
);
</script>

<template>
    <AppLayout>
        <div class="container mx-auto md:px-4 py-4 min-h-screen">
            <h1 class="font-bold text-xl mb-1">
                <Medal class="size-8 inline-block" />
                {{ $t('leaderboard.month') }} {{ monthName(currentMonth()) }}
                <Loading v-if="loading" />
            </h1>

            <div class="flex justify-between">
                <RouterLink
                    :to="{ name: 'leaderboard-monthly', params: { month: previousMonth().toFormat('yyyy-MM') } }"
                    class="btn"
                >
                    <ArrowLeft class="inline-block size-5" />
                    {{ monthName(previousMonth()) }}
                </RouterLink>
                <RouterLink
                    v-if="nextMonth() !== null"
                    :to="{ name: 'leaderboard-monthly', params: { month: nextMonth()?.toFormat('yyyy-MM') } }"
                    class="btn"
                >
                    {{ monthName(nextMonth()) }}
                    <ArrowRight class="inline-block size-5" />
                </RouterLink>
                <div v-else></div>
            </div>

            <template v-if="!loading && selectedData.length > 0">
                <div class="flex justify-end mb-2">
                    <div class="max-w-xs">
                        <select v-model="focusData" class="select flex md:hidden">
                            <option :value="'duration'">{{ $t('leaderboard.duration') }}</option>
                            <option :value="'distance'">{{ $t('leaderboard.distance') }}</option>
                            <option :value="'speed'">{{ $t('leaderboard.averagespeed') }}</option>
                            <option :value="'points'">{{ $t('leaderboard.points') }}</option>
                        </select>
                    </div>
                </div>

                <ul class="list bg-base-100 rounded-box shadow-md">
                    <LeaderboardEntry
                        v-for="(entry, number) in selectedData"
                        :key="entry.user.id"
                        :number
                        :entry
                        :show-distance="focusData === 'distance'"
                        :show-duration="focusData === 'duration'"
                        :show-points="focusData === 'points'"
                        :show-speed="focusData === 'speed'"
                    />
                </ul>
            </template>

            <div v-if="!loading && selectedData.length === 0" class="col-md-12">
                <div class="card">
                    <div class="card-body text-center text-danger text-bold">
                        {{ $t('leaderboard.no_data') }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
