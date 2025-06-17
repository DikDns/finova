<script setup lang="ts">
import AdminSideBarLayout from '@/components/admin/AdminSideBarLayout.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItemType } from '@/types';
import 'vue-sonner/style.css';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <Toaster />

    <AdminSideBarLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AdminSideBarLayout>
</template>
