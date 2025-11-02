<script>
export default {
  name: "LineIndicator",
  props: {
    number: {
      type: String,
      required: true
    },
    productName: {
      type: String,
      required: true
    },
    // optional overrides:
    backgroundColor: {
      type: String,
      required: false,
      default: null
    },
    color: {
      type: String,
      required: false,
      default: null
    }
  },
  data() {
    return {
      products: {
        tram: {
          color: "#D91A1A",
          text: "#fff",
          shape: "rectangle",
        },
        bus: {
          color: "#9d0278",
          text: "#fff",
          shape: "circle",
        },
        suburban: {
          color: "#026c35",
          text: "#fff",
          shape: "rounded",
        },
        subway: {
          color: "#1667b1",
          text: "#fff",
          shape: "rectangle",
        },
        default: {
          color: "#2B2D42",
          text: "#fff",
          shape: "rectangle",
        }
      },
    };
  },
  computed: {
    lineName() {
      return this.$props.number.replace(/STR|Bus/g, "");
    },
    product() {
      return Object.prototype.hasOwnProperty.call(this.products, this.$props.productName)
          ? this.products[this.$props.productName]
          : this.products.default;
    },
    effectiveBackground() {
      return this.$props.backgroundColor ?? this.product.color;
    },
    effectiveText() {
      return this.$props.color ?? this.product.text;
    },
    cssVars() {
      return `--accent: ${this.effectiveBackground}; --contrast: ${this.effectiveText};`;
    }
  }
}
</script>

<template>
  <span
      class="line-indicator"
      :class="product.shape"
      :style="cssVars"
  >{{ lineName }}</span>
</template>

<style scoped lang="scss">
.line-indicator.pill {
  border-radius: 0.6em;
  min-width: 1.75rem !important;
}

.line-indicator.rounded-corner {
  border-radius: 0.3em;
  min-width: 1.75rem !important;
}

.line-indicator.rounded {
  min-width: 1.75rem;
  border-radius: 99rem !important;
}

.line-indicator.circle {
  border-radius: 50%;
  overflow-x: visible;
  height: 1.5rem;
  width: 1.5rem;
}

.line-indicator {
  display: flex;
  flex-shrink: 0;
  justify-content: center;
  align-items: center;
  background-color: var(--accent);
  color: var(--contrast);
  font-size: .75rem;
  height: 1.25rem;
  min-width: 1.5rem;
  padding: 0 .375rem;
}
</style>
