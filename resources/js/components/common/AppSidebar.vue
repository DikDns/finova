<script setup lang="ts">
import FinovaLogo from '@/components/common/FinovaLogo.vue';
import NavMain from '@/components/common/NavMain.vue';
import NavUser from '@/components/common/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { ChartBar, CreditCard, Wallet } from 'lucide-vue-next';

const props = defineProps<{
    budget_id: string;
}>();

const mainNavItems: NavItem[] = [
    {
        title: 'Budget',
        href: route('budget', props.budget_id),
        icon: Wallet,
    },
    {
        title: 'Analisis',
        href: route('budget.analysis', props.budget_id),
        icon: ChartBar,
    },
    {
        title: 'Semua Rekening',
        href: route('budget.accounts', props.budget_id),
        icon: CreditCard,
    },
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
