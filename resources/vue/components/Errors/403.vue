<script setup lang="ts">
import {trans} from "laravel-vue-i18n";

function goHome() {
  window.location.href = "/";
}
</script>

<template>
  <div class="card border-0 shadow-sm">
    <div class="ratio ratio-16x9">
      <!-- ToDo: Hier wäre ein Bild einer Sh2-Tafel sehr nice. So wie das hier in etwa: https://www.istockphoto.com/de/foto/bahn-buffer-zug-gm1138134590-303733249 -->
      <img
          src="/images/covers/hp0.jpg"
          class="card-img-top object-fit-cover"
          style="object-position: bottom;"
          :alt="trans('errors.403.alt_text')"
      >
    </div>
    <div class="card-body text-center py-5">
      <div class="display-6 mb-3" aria-hidden="true">🔒</div>
      <h1 class="h4 mb-2">{{ trans("error.403") }}</h1>
      <p class="text-muted">
        {{ trans("errors.403.lead") }}
      </p>

      <button type="button" class="btn btn-primary" @click="goHome">
        {{ trans("errors.actions.home") }}
      </button>
    </div>
  </div>
</template>
