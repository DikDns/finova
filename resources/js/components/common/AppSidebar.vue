<script setup lang="ts">
import FinovaLogo from '@/components/common/FinovaLogo.vue';
import NavMain from '@/components/common/NavMain.vue';
import NavUser from '@/components/common/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuItem } from '@/components/ui/sidebar';
import { useSidebar } from '@/components/ui/sidebar/utils';
import { type AccountType, type NavItem } from '@/types';
import { ChartBar, CreditCard, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    budget_id: string;
    currency_code: string;
    account_types?: AccountType[];
}>();

const { state } = useSidebar();
const isCollapsed = computed(() => state.value === 'collapsed');

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

const accountTypes = props.account_types || [];
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
            <NavMain :items="mainNavItems" :account_types="accountTypes" :currency_code="currency_code" :budget_id="budget_id" :isCollapsed="isCollapsed" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
