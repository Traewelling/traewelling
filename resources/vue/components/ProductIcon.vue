<script>
export default {
    name: "ProductIcon",
    props: {
        product: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            iconPaths: {
                tram: "/img/tram.svg",
                bus: "/img/bus.svg",
                subway: "/img/subway.svg",
                suburban: "/img/suburban.svg",
            }
        };
    },
    computed: {
        productExists() {
            return this.iconPaths.hasOwnProperty(this.$props.product);
        },
        fontAwesomeIcon() {
            if (this.$props.product === 'taxi') {
                return 'fa-taxi';
            }
            if (this.$props.product === 'ferry') {
                return 'fa-ship';
            }
            return 'fa-train';
        }
    }
}
</script>

<template>
    <img v-if="productExists"
         :alt="this.$props.product"
         :src="this.iconPaths[this.$props.product]"
         class="product-icon">
    <i v-else class="fas" :class="fontAwesomeIcon"></i>
</template>
