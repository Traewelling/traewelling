<template>
    <div class="card">
        <div class="card-header">{{ title }}</div>
        <div class="card-body">
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: "Card",
    props: ["title"]
};
</script>

<style scoped>

</style>
