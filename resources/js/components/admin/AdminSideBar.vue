<script setup lang="ts">
import FinovaLogo from '@/components/common/FinovaLogo.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ChevronUp, LayoutDashboard, LogOut, Menu, Receipt, Settings, WalletCards, X } from 'lucide-vue-next';
import { ref, type Component } from 'vue';

// Sidebar Item Type
type SidebarItem = {
    title: string;
    href: string;
    icon: Component;
};

// Sidebar Menu Items
const items: SidebarItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
    { title: 'Account', href: '/admin/account', icon: WalletCards },
    { title: 'Subscription', href: '/admin/subscription', icon: Receipt },
];

const props = usePage<SharedData>();

// Sidebar State
const isMobileSidebarOpen = ref(false);

// Handlers
const toggleMobileSidebar = () => {
    isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
};

const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false;
};

const handleLogout = () => {
    console.log('Logging out...');
    closeMobileSidebar();
};
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Mobile Toggle Button -->
        <button
            @click="toggleMobileSidebar"
            class="fixed top-4 left-4 z-[60] rounded-md border border-gray-200 bg-white p-2 shadow-lg transition-colors duration-200 hover:bg-gray-50 lg:hidden"
            :aria-label="isMobileSidebarOpen ? 'Close menu' : 'Open menu'"
        >
            <Menu v-if="!isMobileSidebarOpen" class="h-5 w-5 text-gray-700" />
            <X v-else class="h-5 w-5 text-gray-700" />
        </button>

        <!-- Mobile Overlay -->
        <div
            v-if="isMobileSidebarOpen"
            @click="closeMobileSidebar"
            class="bg-opacity-50 fixed inset-0 z-40 bg-black transition-opacity duration-300 lg:hidden"
        ></div>

        <!-- Sidebar -->
        <Sidebar
            variant="inset"
            :class="[
                'border-r border-gray-200 shadow-lg transition-all duration-300 ease-in-out',
                'fixed z-50 flex h-full flex-col',
                'lg:translate-x-0',
                isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Header -->
            <SidebarHeader class="flex-shrink-0">
                <div class="flex items-center justify-between px-2">
                    <SidebarMenu>
                        <SidebarMenuItem class="flex w-full items-center gap-2 overflow-hidden text-left text-sm outline-hidden">
                            <FinovaLogo />
                        </SidebarMenuItem>
                    </SidebarMenu>
                </div>
            </SidebarHeader>

            <!-- Content -->
            <SidebarContent class="flex-1 overflow-y-auto">
                <SidebarGroup>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem v-for="item in items" :key="item.title">
                                <SidebarMenuButton
                                    as-child
                                    class="text-neutral-700 transition-colors duration-200 hover:bg-neutral-100 hover:text-neutral-900 active:bg-neutral-200"
                                >
                                    <a :href="item.href" @click="closeMobileSidebar">
                                        <component :is="item.icon" />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>

            <!-- Footer -->
            <SidebarFooter class="flex-shrink-0 border-t border-gray-200 bg-white">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <SidebarMenuButton size="lg" class="transition-colors duration-200 hover:bg-neutral-100">
                                    <div
                                        class="flex aspect-square size-8 items-center justify-center rounded-full bg-neutral-600 text-sm font-medium text-white"
                                    >
                                        {{ adminProfile.avatar }}
                                    </div>
                                    <div class="grid flex-1 text-left text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ adminProfile.name }}</span>
                                        <span class="truncate text-xs text-neutral-500">{{ adminProfile.email }}</span>
                                    </div>
                                    <ChevronUp class="ml-auto size-4" />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent
                                class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg shadow-lg"
                                side="top"
                                align="end"
                                :sideOffset="4"
                            >
                                <DropdownMenuItem class="cursor-pointer" @click="closeMobileSidebar">
                                    <Settings class="mr-2 h-4 w-4" />
                                    <span>Account Settings</span>
                                </DropdownMenuItem>

                                <DropdownMenuSeparator />

                                <DropdownMenuItem @click="handleLogout" class="cursor-pointer text-red-600 focus:bg-red-50 focus:text-red-600">
                                    <LogOut class="mr-2 h-4 w-4" />
                                    <span>Log out</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    </div>
</template>
