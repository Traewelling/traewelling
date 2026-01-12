<script setup lang="ts">

import {computed} from "vue";
import {MotisCategory} from "../../types/Api.gen";

const props = defineProps({
  number: {
    type: String,
    required: true
  },
  mode: {
    type: String as () => MotisCategory | null,
    required: false,
    default: null
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
  },
  className: {
    type: String,
    required: false,
    default: 'line-indicator full'
  }
});

const products = {
  tram: {
    color: "#D91A1A",
    text: "#fff",
    shape: "rectangle",
  },
  bus: {
    color: "#9d0278",
    text: "#fff",
    shape: "rounded",
  },
  bus_pill: {
    color: "#9d0278",
    text: "#fff",
    shape: "rounded",
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
};

const motisMapping: Record<MotisCategory, string> = {
  [MotisCategory.WALK]: 'default',
  [MotisCategory.BIKE]: 'default',
  [MotisCategory.RENTAL]: 'default',
  [MotisCategory.CAR]: 'default',
  [MotisCategory.CAR_PARKING]: 'default',
  [MotisCategory.CAR_DROPOFF]: 'default',
  [MotisCategory.ODM]: 'default',
  [MotisCategory.RIDE_SHARING]: 'default',
  [MotisCategory.FLEX]: 'bus',
  [MotisCategory.TRANSIT]: 'tram',
  [MotisCategory.TRAM]: 'tram',
  [MotisCategory.SUBWAY]: 'subway',
  [MotisCategory.FERRY]: 'default',
  [MotisCategory.AIRPLANE]: 'default',
  [MotisCategory.SUBURBAN]: 'suburban',
  [MotisCategory.BUS]: 'bus',
  [MotisCategory.COACH]: 'bus',
  [MotisCategory.RAIL]: 'default',
  [MotisCategory.HIGHSPEED_RAIL]: 'default',
  [MotisCategory.LONG_DISTANCE]: 'default',
  [MotisCategory.NIGHT_RAIL]: 'default',
  [MotisCategory.REGIONAL_FAST_RAIL]: 'default',
  [MotisCategory.REGIONAL_RAIL]: 'default',
  [MotisCategory.CABLE_CAR]: 'tram',
  [MotisCategory.FUNICULAR]: 'tram',
  [MotisCategory.AERIAL_LIFT]: 'tram',
  [MotisCategory.OTHER]: 'default',
  [MotisCategory.AERAL_LIFT]: 'default',
  [MotisCategory.METRO]: 'suburban',
};

const lineName = computed((): string => {
  return props.number.replaceAll(/STR|Bus/g, "");
});

const product = computed(() => {
  let productName = null;
  // remap via motis category if possible
  if (props.mode && Object.hasOwn(motisMapping, props.mode)) {
    productName = motisMapping[props.mode as keyof typeof motisMapping];
  } else {
    productName = props.productName;
  }

  if (productName === "bus" && lineName.value.length > 3) {
    productName = "bus_pill";
  }

  return Object.hasOwn(products, productName)
      ? products[productName as keyof typeof products]
      : products.default;
});

function normalizeHexColor(hex?: string | string): string | null {
  if (!hex) return null;
  const clean = String(hex).replace(/[^0-9a-fA-F]/g, "");
  if (clean.length !== 6) return null;
  return `#${clean}`;
}

const effectiveBackground = computed((): string => {
  return normalizeHexColor(props.backgroundColor) ? normalizeHexColor(props.backgroundColor)! : product.value.color;
});
const effectiveText = computed((): string => {
  return normalizeHexColor(props.color) && normalizeHexColor(props.backgroundColor) ? normalizeHexColor(props.color)! : product.value.text;
});
const cssVars = computed((): string => {
  return `--accent: ${effectiveBackground.value}; --contrast: ${effectiveText.value};`;
});

</script>

<template>
  <span
      :class="[product.shape, className]"
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

.line-indicator {
  background-color: var(--accent);
  color: var(--contrast);
  font-size: .75rem;
  min-width: 1.5rem;
}

.line-indicator.full {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 .375rem;
  height: 1.25rem;
}

.line-indicator.line-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.05rem 0.35rem;
  margin: 0 0.25rem 0 0.35rem;
  border-radius: 0.35rem;
  line-height: 1.1;
  font-weight: 600;
  font-size: 0.95em;
  vertical-align: baseline;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.06) inset;
}
</style>
