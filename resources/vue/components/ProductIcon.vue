<script lang="ts" setup>
defineProps({
  product: {
    type: String,
    default: '',
    required: true
  }
});

const iconForProduct = (product: string) => {
  if (['tram', 'bus', 'subway', 'suburban'].includes(product)) {
    return `/img/${product}.svg`;
  }

  return null;
};

const fontAwesomeIcon = (product: string) => {
  switch (product) {
    case 'taxi':
      return 'fa-taxi';
    case 'plane':
      return 'fa-plane';
    case 'ferry':
      return 'fa-ship';
    default:
      return 'fa-train';
  }
};
</script>

<template>
  <img v-if="iconForProduct(product)"
       :alt="product"
       :src="iconForProduct(product) || ''"
       class="product-icon">
  <i v-else class="fas" :class="fontAwesomeIcon(product)"></i>
</template>
