<template>
    <div class="card bg-base-200 shadow-sm w-full">
        <div class="card-body p-6 sm:p-8">
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
                        <span class="font-bold text-sm text-white drop-shadow-black drop-shadow-xs">
                            {{ Math.round(store.progressPercent) }}%
                        </span>
                    </div>
                </div>
            </div>

            <ul class="list bg-base-100 rounded-box shadow-none">
                <li class="list-row">
                    <Star class="text-warning w-6 h-6 m-auto" />
                    <div>
                        {{ trans('contribute.profile.total_xp') }}
                    </div>
                    <div class="font-bold text-primary text-lg">
                        {{ store.xp }}
                    </div>
                </li>
                <li class="list-row">
                    <Trophy class="text-warning w-6 h-6 m-auto" />
                    <div>
                        {{ trans('contribute.profile.current_level') }}
                    </div>
                    <div class="font-bold text-primary text-lg">
                        {{ store.level }}
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Star, Trophy } from 'lucide-vue-next';
import { onMounted } from 'vue';
import { useContributeStore } from '../../vue/stores/contribute';

const store = useContributeStore();

onMounted(() => {
    store.fetchProfile();
});
</script>
