<template>
    <div class="d-flex flex-column flex-shrink-0 p-3" style="width: 280px;">
        <SidebarNavContent desktop="true"/>
    </div>
</template>

<script>
import SidebarNavContent from "./SidebarNavContent";

export default {
    name: "SidebarNav",
    components: {SidebarNavContent}
}
</script>

<style scoped>

</style>
