<script setup lang="ts">
import AdminShell from '@/components/admin/AdminShell.vue';
import AdminSideBar from '@/components/admin/AdminSideBar.vue';
</script>

<template>
    <AdminShell variant="sidebar">
        <AdminSideBar />
            <slot />
    </AdminShell>
</template>