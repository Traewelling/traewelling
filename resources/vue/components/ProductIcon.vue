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
        }
    }
}
</script>

<template>
    <img v-if="productExists"
        alt="tram"
        :src="this.iconPaths[this.$props.product]"
        class="product-icon">
    <i v-else class="fa fa-train"></i>
</template>

<style scoped lang="scss">

</style>
