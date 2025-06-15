<script setup lang="ts">
import AppContent from '@/components/common/AppContent.vue';
import AppShell from '@/components/common/AppShell.vue';
import AppSidebar from '@/components/common/AppSidebar.vue';
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
        <AppSidebar :budget_id="budget_id" :currency_code="currency_code" :account_types="account_types" />
        <AppContent variant="sidebar">
            <AppSidebarHeader />
            <slot />
        </AppContent>
    </AppShell>
</template>
