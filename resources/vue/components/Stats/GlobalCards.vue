<template>
  <div class="global-cards row">
    <div class="col-lg-4 mb-4">
      <div class="card">
        <div class="card-body text-center">
          <i class="fas fa-ruler fa-4x mt-1" aria-hidden="true"></i>
          <div>
            <span class="font-weight-bold fs-2">{{ (stats.distance / 1000).toFixed(0) }} km</span>
            <br>
            <small class="text-muted">{{ trans('stats.global.distance') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-4">
      <div class="card">
        <div class="card-body text-center">
          <i class="fas fa-clock fa-4x mt-1" aria-hidden="true"></i>
          <div>
            <span v-html="formatDuration(stats.duration)"></span>
            <br>
            <small class="text-muted">{{ trans('stats.global.duration') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-4">
      <div class="card">
        <div class="card-body text-center">
          <i class="fas fa-users fa-4x mt-1" aria-hidden="true"></i>
          <div>
            <span class="font-weight-bold fs-2">{{ stats.user_count }}x</span>
            <br>
            <small class="text-muted">{{ trans('stats.global.active') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 text-center">
      <small class="text-muted">*{{ trans('stats.global.explain', { fromDate: formatDate(from), toDate: formatDate(to) }) }}</small>
    </div>
  </div>
</template>

<script>
import { trans } from 'laravel-vue-i18n';

export default {
  name: 'GlobalCards',
  props: {
    stats: { type: Object, required: true },
    from: { type: String, required: true },
    to: { type: String, required: true }
  },
  methods: {
    trans,
    formatDate(d) {
      return new Date(d).toLocaleDateString();
    },
    formatDuration(sec) {
      const h = Math.floor(sec / 3600);
      const m = Math.floor((sec % 3600) / 60);
      return `<span class=\"font-weight-bold fs-2\">${h}h ${m}m</span>`;
    }
  }
};
</script>
