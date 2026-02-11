<template>
    <div class="w-full">
        <h1 class="text-4xl font-bold mb-8 text-base-content">{{ trans('contribute.profile.title') }}</h1>

        <div v-if="store.loading" class="flex justify-center items-center min-h-[400px]">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <div v-else class="card bg-base-200 shadow-xl">
            <div class="card-body p-6 sm:p-8">
                <div class="text-center mb-8 py-6">
                    <div class="mb-4">
                        <i class="fa fa-trophy text-warning text-6xl drop-shadow-lg"></i>
                    </div>
                    <div class="text-5xl font-bold text-primary">
                        {{ trans('contribute.levels.level') }} {{ store.level }}
                    </div>
                </div>

                <div class="mb-8">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-semibold text-base-content">{{ trans('contribute.profile.xp') }}</span>
                        <span class="text-base-content opacity-60 text-sm"
                            >{{ store.xp }} / {{ store.nextLevelXP }} XP</span
                        >
                    </div>
                    <div class="relative">
                        <progress
                            class="progress progress-success w-full h-8"
                            :value="store.progressPercent"
                            max="100"
                        ></progress>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="font-bold text-sm text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">
                                {{ Math.round(store.progressPercent) }}%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stats stats-vertical lg:stats-horizontal shadow-lg mb-6 w-full stat-cards bg-base-100">
                    <div class="stat stat-card place-items-center">
                        <div class="stat-figure text-warning">
                            <i class="fa fa-star text-5xl"></i>
                        </div>
                        <div class="stat-title text-base-content opacity-70">
                            {{ trans('contribute.profile.total_xp') }}
                        </div>
                        <div class="stat-value text-primary">{{ store.xp }}</div>
                    </div>

                    <div class="stat stat-card place-items-center">
                        <div class="stat-figure text-warning">
                            <i class="fa fa-trophy text-5xl"></i>
                        </div>
                        <div class="stat-title text-base-content opacity-70">
                            {{ trans('contribute.profile.current_level') }}
                        </div>
                        <div class="stat-value text-primary">{{ store.level }}</div>
                    </div>
                </div>

                <div class="text-center mt-6">
                    <a href="/contribute" class="btn btn-outline gap-2">
                        <i class="fa fa-arrow-left"></i>
                        {{ trans('contribute.profile.back_to_overview') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { onMounted } from 'vue';
import { useContributeStore } from '../../stores/contribute';

const store = useContributeStore();

onMounted(() => {
    store.fetchProfile();
});
</script>
