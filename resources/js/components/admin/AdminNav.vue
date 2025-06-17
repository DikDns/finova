<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
    isCollapsed?: boolean;
}>();

const page = usePage<SharedData>();

const getThePathOnly = (href: string) => {
    return '/' + href.split('/').slice(3).join('/');
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Admin</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <!-- The item.href is contain the domain remove it -->
                <SidebarMenuButton as-child :is-active="getThePathOnly(item.href) === page.url" :tooltip="item.title">
                    <Link :href="item.href" prefetch>
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
