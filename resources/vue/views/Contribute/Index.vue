<template>
    <div class="w-full">
        <h1 class="text-4xl font-bold mb-8 text-base-content">{{ trans('contribute.title') }}</h1>

        <div class="card bg-base-200 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title text-2xl text-base-content">{{ trans('contribute.hero.title') }}</h2>
                <p class="text-base-content opacity-80 leading-relaxed">{{ trans('contribute.hero.description') }}</p>
            </div>
        </div>

        <div class="card bg-base-200 shadow-xl mb-8">
            <div class="card-body">
                <h3 class="card-title text-xl text-base-content">{{ trans('contribute.how_it_works.title') }}</h3>
                <ul class="list-disc list-inside space-y-3 text-base-content opacity-80 ml-2">
                    <li>{{ trans('contribute.how_it_works.step1') }}</li>
                    <li>{{ trans('contribute.how_it_works.step2') }}</li>
                    <li>{{ trans('contribute.how_it_works.step3') }}</li>
                </ul>
            </div>
        </div>

        <div class="text-center">
            <a href="/contribute/profile" class="btn btn-primary btn-lg gap-2">
                <i class="fa fa-user"></i>
                {{ trans('contribute.view_profile') }}
            </a>
        </div>
    </div>
</template>

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
</script>
