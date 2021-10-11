<template>
    <div>
        <NavbarComponent/>
        <main class="py-4">
            <div class="container">
                <div class="row mb-3">
                    <div class="col-md-3 d-none d-md-block">
                        <SidebarNav/>
                    </div>
                    <slot/>
                </div>
                <OffCanvasNavigation/>
            </div>
        </main>
        <FooterComponent hideFooter="false"/>
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
    name: "LayoutBasic",
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
