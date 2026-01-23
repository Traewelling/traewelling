<script setup lang="ts">
import Navbar from './Navbar/Navbar.vue';
import Footer from './Footer/Footer.vue';
</script>

<template>
    <Navbar/>
    <main class="py-4" role="main">
        <slot></slot>
    </main>
    <Footer/>
</template>
