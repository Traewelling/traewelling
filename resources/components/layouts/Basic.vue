<template>
    <div>
        <NavbarComponent/>
        <main class="py-4">
            <div class="container">
                <slot/>
            </div>
        </main>
        <FooterComponent/>
    </div>
</template>

<script>
import NavbarComponent from "./NavbarComponent";
import FooterComponent from "./FooterComponent";

export default {
    name: "LayoutBasic",
    components: {
        FooterComponent,
        NavbarComponent,
    }
};
</script>

<style scoped>

</style>
