<template>
    <div>
        <NavbarComponent/>
        <main class="py-4">
            <div class="container">
                <slot/>
            </div>
        </main>
        <FooterComponent/>
        <MobileFooterNav/>
    </div>
</template>

<script>
import NavbarComponent from "./NavbarComponent";
import FooterComponent from "./FooterComponent";
import MobileFooterNav from "./MobileFooterNav";

export default {
    name: "LayoutBasic",
    components: {
        MobileFooterNav,
        FooterComponent,
        NavbarComponent,
    }
};
</script>

<style scoped>

</style>
