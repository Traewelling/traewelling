<template>
    <div>
        <NavbarComponent/>
        <main class="py-4">
            <div class="container">
                <slot/>
                <OffCanvasNavigation/>
            </div>
        </main>
        <FooterComponent :class="footerclass"/>
        <MobileFooterNav/>
    </div>
</template>

<script>
import NavbarComponent from "./NavbarComponent";
import FooterComponent from "./FooterComponent";
import MobileFooterNav from "./MobileFooterNav";
import OffCanvasNavigation from "./OffCanvasNavigation";
import SidebarNav from "./SidebarNav";

export default {
    name: "LayoutBasicNoSidebar",
    props: ["footerclass"],
    components: {
        SidebarNav,
        OffCanvasNavigation,
        MobileFooterNav,
        FooterComponent,
        NavbarComponent,
    }
};
</script>

<style scoped>

</style>
