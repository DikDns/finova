<script setup lang="ts">
import AppContent from '@/components/common/AppContent.vue';
import AppShell from '@/components/common/AppShell.vue';
import AppSidebar from '@/components/common/AppSidebar.vue';
import AppSidebarHeader from '@/components/common/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
