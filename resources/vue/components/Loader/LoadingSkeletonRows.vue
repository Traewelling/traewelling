<script>
export default {
  name: "LoadingSkeletonRows",
  inheritAttrs: true,
  props: {
    rows: {type: Number, default: 3},
    columns: {type: Number, default: 1},
    rowHeight: {type: Number, default: 56},
    gutter: {type: Number, default: 12},
    ariaLabel: {type: String, default: null},
    borderRadius: {type: String, default: ".5rem"}
  },
  computed: {
    rootAria() {
      return this.ariaLabel
          ? {role: "status", "aria-live": "polite", "aria-label": this.ariaLabel}
          : {"aria-hidden": "true"};
    },
    cssVars() {
      return {
        "--ls-row-height": `${this.rowHeight}px`,
        "--ls-gutter": `${this.gutter}px`,
        "--ls-columns": `${Math.max(1, this.columns)}`,
        "--ls-border-radius": this.borderRadius
      };
    },
    list() {
      return Array.from({length: Math.max(0, this.rows)});
    },
    colList() {
      return Array.from({length: Math.max(1, this.columns)});
    }
  }
};
</script>

<template>
  <div class="ls-wrap" v-bind="rootAria" :style="cssVars">
    <div
        v-for="(_, i) in list"
        :key="'row-' + i"
        class="ls-row-group"
    >
      <div
          v-for="(_, j) in colList"
          :key="'col-' + j"
          class="ls-row ls-animate"
      />
    </div>
  </div>
</template>

<style scoped lang="scss">
.ls-wrap {
  width: 100%;
}

.ls-row-group {
  display: grid;
  grid-template-columns: repeat(var(--ls-columns), minmax(0, 1fr));
  gap: var(--ls-gutter);
  margin-bottom: var(--ls-gutter);
}

.ls-row {
  height: var(--ls-row-height);
  width: 100%;
  display: block;
  border-radius: var(--ls-border-radius);
  background: linear-gradient(
          90deg,
          rgba(0, 0, 0, .06) 25%,
          rgba(0, 0, 0, .12) 37%,
          rgba(0, 0, 0, .06) 63%
  );
  background-size: 400% 100%;
}

.ls-animate {
  animation: shimmer 1.2s linear infinite;
}

/* Dark Mode */
:root.dark .ls-row {
  background: linear-gradient(
          90deg,
          rgba(255, 255, 255, .06) 25%,
          rgba(255, 255, 255, .12) 37%,
          rgba(255, 255, 255, .06) 63%
  );
  background-size: 400% 100%;
}

@media (prefers-reduced-motion: reduce) {
  .ls-animate {
    animation: none;
  }
}

@keyframes shimmer {
  0% {
    background-position: 100% 0;
  }
  100% {
    background-position: 0 0;
  }
}
</style>
