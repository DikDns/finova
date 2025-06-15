<script setup lang="ts">
import UserInfo from '@/components/common/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, LayoutDashboard, LogOut, Settings, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

const page = usePage();
const isAdminRoute = computed(() => page.url.startsWith('/admin'));

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup v-if="user.role === 'admin'">
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="isAdminRoute ? route('budgets') : route('admin.dashboard')" prefetch as="button">
                <LayoutDashboard v-if="!isAdminRoute" class="mr-2 h-4 w-4" />
                <ArrowLeft v-else class="mr-2 h-4 w-4" />
                {{ isAdminRoute ? 'Kembali ke Aplikasi' : 'Admin Dashboard' }}
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('budgets')" prefetch as="button">
                <Wallet class="mr-2 h-4 w-4" />
                Kelola Budget
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('profile.edit')" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Pengaturan
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Keluar
        </Link>
    </DropdownMenuItem>
</template>
