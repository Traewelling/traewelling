<template>
    <ContributeLayout>
        <div v-if="user.user" class="w-full">
            <h1 class="font-title text-2xl md:text-3xl lg:text-4xl font-bold">
                {{ trans('contribute.title', { username: user.user.displayName }) }}
            </h1>
            <h2 class="mb-2 md:mb-6">
                {{ trans('contribute.hero.title') }}
            </h2>
            <div role="alert" class="alert alert-vertical sm:alert-horizontal mb-6">
                <Info class="stroke-info h-6 w-6" />
                <span>
                    {{ trans('contribute.hero.description') }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                <!-- left grid -->
                <div class="col-span-1 md:col-span-4 space-y-6">
                    <div class="card bg-base-200 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-xl text-base-content">
                                {{ trans('contribute.how_it_works.title') }}
                            </h3>
                            <ul class="list-disc list-inside space-y-3 text-base-content opacity-80 ml-2">
                                <li>{{ trans('contribute.how_it_works.step1') }}</li>
                                <li>{{ trans('contribute.how_it_works.step2') }}</li>
                                <li>{{ trans('contribute.how_it_works.step3') }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <router-link
                            to="/contribute/event-proposal"
                            class="card bg-base-200 shadow-sm hover:shadow-md transition-shadow"
                        >
                            <div class="card-body">
                                <h3 class="card-title text-lg">
                                    <CalendarPlus class="w-5 h-5" />
                                    {{ trans('contribute.index.suggest_event') }}
                                </h3>
                                <p class="text-base-content opacity-70 text-sm">
                                    {{ trans('contribute.index.suggest_event_description') }}
                                </p>
                            </div>
                        </router-link>
                    </div>
                </div>

                <!-- right sidebar -->
                <div class="col-span-1 md:col-span-2 space-y-6">
                    <ContributionStats />
                    <ContributionHistory :limit="5" />
                </div>
            </div>
        </div>
    </ContributeLayout>
</template>

<script setup lang="ts">
import { CalendarPlus, Info } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { onMounted } from 'vue';
import { useUserStore } from '../../../vue/stores/user';
import ContributionHistory from '../../components/ContributionHistory.vue';
import ContributionStats from '../../components/ContributionStats.vue';
import ContributeLayout from '../../layouts/ContributeLayout.vue';

const user = useUserStore();

onMounted(() => {
    user.fetchSettings();
});
</script>
