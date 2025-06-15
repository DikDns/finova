<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { AccountType, BreadcrumbItemType } from '@/types';
import 'vue-sonner/style.css';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    budget_id: string;
    currency_code: string;
    account_types?: AccountType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <Toaster />

    <AppLayout :breadcrumbs="breadcrumbs" :budget_id="budget_id" :currency_code="currency_code" :account_types="account_types">
        <slot />
    </AppLayout>
</template>
