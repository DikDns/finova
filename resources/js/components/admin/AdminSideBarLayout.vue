<script setup lang="ts">
import AdminShell from '@/components/admin/AdminShell.vue';
import AdminSideBar from '@/components/admin/AdminSideBar.vue';
import AppContent from '@/components/common/AppContent.vue';
import AppSidebarHeader from '@/components/common/AppSidebarHeader.vue';
</script>

<template>
    <AdminShell variant="sidebar">
        <AdminSideBar />
        <AppContent variant="sidebar">
            <AppSidebarHeader />
            <slot />
        </AppContent>
    </AdminShell>
</template>
