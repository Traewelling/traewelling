<script setup lang="ts">
import LoginForm from '../../components/Auth/LoginForm.vue';
import AppLayout from '../../layouts/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <div class="card bg-base-100 shadow-sm mx-auto w-full max-w-md">
            <div class="card-body">
                <LoginForm />
            </div>
        </div>
    </AppLayout>
</template>
