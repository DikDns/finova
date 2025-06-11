<script setup lang="ts">
import FinovaLogo from '@/components/common/FinovaLogo.vue';
import NavUser from '@/components/common/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { Book, Video } from 'lucide-vue-next';
import { type Component } from 'vue';

type SidebarItem = {
    title: string;
    href: string;
    icon: Component;
};

const items: SidebarItem[] = [
    {
        title: 'Nonton Video Tutorial',
        href: 'https://www.youtube.com',
        icon: Video,
    },
    {
        title: 'Baca Artikel Relevan',
        href: 'https://example.com',
        icon: Book,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem class="flex w-full items-center gap-2 overflow-hidden text-left text-sm outline-hidden">
                    <FinovaLogo />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent> </SidebarContent>

        <SidebarFooter>
            <SidebarGroup :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`">
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="item in items" :key="item.title">
                            <SidebarMenuButton class="text-neutral-400 hover:text-neutral-800" as-child>
                                <a :href="item.href" target="_blank" rel="noopener noreferrer">
                                    <component :is="item.icon" />
                                    <span>{{ item.title }}</span>
                                </a>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
