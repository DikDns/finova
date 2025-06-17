<script setup lang="ts">
import FinovaLogo from '@/components/common/FinovaLogo.vue';
import NavUser from '@/components/common/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuItem } from '@/components/ui/sidebar';
import { useSidebar } from '@/components/ui/sidebar/utils';
import { type NavItem } from '@/types';
import { LayoutDashboard, Receipt, WalletCards } from 'lucide-vue-next';
import { computed } from 'vue';
import AdminNav from './AdminNav.vue';

const { state } = useSidebar();
const isCollapsed = computed(() => state.value === 'collapsed');

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('admin.dashboard'),
        icon: LayoutDashboard,
    },
    { title: 'Account', href: route('admin.account'), icon: WalletCards },
    { title: 'Subscription', href: route('admin.subscription'), icon: Receipt },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuItem class="flex w-full items-center gap-2 overflow-hidden text-left text-sm outline-hidden">
                        <FinovaLogo />
                    </SidebarMenuItem>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <AdminNav :items="mainNavItems" :isCollapsed="isCollapsed" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
