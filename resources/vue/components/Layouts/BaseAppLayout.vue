<script setup lang="ts">
import Footer from './Footer/Footer.vue';
import Navbar from './Navbar/Navbar.vue';
</script>

<template>
    <Navbar />
    <main class="py-4" role="main">
        <slot></slot>
    </main>
    <Footer />
</template>
