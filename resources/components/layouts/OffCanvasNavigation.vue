<template>
    <div id="offcanvasNavigation" aria-labelledby="offcanvasNavigationLabel" class="offcanvas offcanvas-start"
         tabindex="-1">
        <div class="offcanvas-header">
            <h5 id="offcanvasNavigationLabel" class="offcanvas-title">Träwelling</h5><!--ToDo: Get name from .env -->
            <button aria-label="Close" class="btn-close text-reset" data-mdb-dismiss="offcanvas"
                    type="button"></button>
        </div>
        <div class="offcanvas-body">
            <SidebarNavContent/>
        </div>
    </div>
</template>

<script>
import SidebarNavContent from "./SidebarNavContent";

export default {
    name: "offcanvasNavigation",
    components: {SidebarNavContent}
};
</script>

<style scoped>

</style>
