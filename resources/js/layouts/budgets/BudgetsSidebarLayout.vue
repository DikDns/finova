<script setup lang="ts">
import BudgetsSidebar from '@/components/budgets/BudgetsSidebar.vue';
import AppContent from '@/components/common/AppContent.vue';
import AppShell from '@/components/common/AppShell.vue';
import AppSidebarHeader from '@/components/common/AppSidebarHeader.vue';
</script>

<template>
    <AppShell variant="sidebar">
        <BudgetsSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader />
            <slot />
        </AppContent>
    </AppShell>
</template>
