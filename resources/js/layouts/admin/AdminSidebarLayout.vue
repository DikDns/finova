<script setup lang="ts">
import AdminSideBar from '@/components/admin/AdminSideBar.vue';
import AppContent from '@/components/common/AppContent.vue';
import AppShell from '@/components/common/AppShell.vue';
import AppSidebarHeader from '@/components/common/AppSidebarHeader.vue';
import type { AccountType } from '@/types';
import { defineProps } from 'vue';

defineProps<{
    budget_id: string;
    currency_code: string;
    account_types?: AccountType[];
}>();
</script>

<template>
    <AppShell variant="sidebar">
        <AdminSideBar />
        <AppContent variant="sidebar">
            <AppSidebarHeader />
            <slot />
        </AppContent>
    </AppShell>
</template>
