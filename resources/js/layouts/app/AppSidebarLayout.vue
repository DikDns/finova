<script setup lang="ts">
import AppContent from '@/components/common/AppContent.vue';
import AppShell from '@/components/common/AppShell.vue';
import AppSidebar from '@/components/common/AppSidebar.vue';
import AppSidebarHeader from '@/components/common/AppSidebarHeader.vue';
import { defineProps } from 'vue';

defineProps<{
    budget_id: string;
}>();
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar :budget_id="budget_id" />
        <AppContent variant="sidebar">
            <AppSidebarHeader />
            <slot />
        </AppContent>
    </AppShell>
</template>
