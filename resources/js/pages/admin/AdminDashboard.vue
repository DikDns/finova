<script setup lang="ts">
// Import Vue features
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Import components
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { ChevronLeft, ChevronRight, Receipt, Search, Users } from 'lucide-vue-next';

// Define interfaces for type safety
interface UserLogEntry {
    id: string;
    user: string;
    user_name: string;
    action: string;
    description: string;
    ipAddress: string;
    userAgent: string;
    oldValues?: any;
    newValues?: any;
    createdAt: string;
    updatedAt: string;
}

interface UserLogsData {
    data: UserLogEntry[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

// Props from server
const props = defineProps<{
    userLogs: UserLogsData;
    filters: {
        search?: string;
        per_page: number;
    };
    totalUsers: number;
    totalSubscriptions: number;
    usersGrowth: number;
    subscriptionsGrowth: number;
}>();

// State variables
const searchQuery = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);

// Perform a search and update the view
const search = () => {
    router.get(
        route('admin.dashboard'),
        {
            search: searchQuery.value,
            per_page: perPage.value,
            page: 1,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

// Go to a specific page
const goToPage = (page: number) => {
    router.get(
        route('admin.dashboard'),
        {
            search: searchQuery.value,
            per_page: perPage.value,
            page: page,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

// Format long user agent strings
const formatUserAgent = (userAgent: string) => {
    if (!userAgent) return 'Unknown';
    return userAgent.length > 50 ? userAgent.substring(0, 50) + '...' : userAgent;
};

// Debounce the search input
let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        search();
    }, 500);
});

// Track which log row is expanded
const selectedLogId = ref<string | null>(null);

// Toggle the expanded detail row
const toggleRowDetail = (id: string) => {
    selectedLogId.value = selectedLogId.value === id ? null : id;
};

// Format old/new values as readable JSON
const formatJsonValues = (value: any) => {
    try {
        return JSON.stringify(value, null, 2);
    } catch {
        return '-';
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <div class="px-2 pt-8 pb-10 sm:px-4 lg:px-13">
            <div class="w-full">
                <!-- Header Section -->
                <div class="mb-4 sm:mb-6"></div>
                <h1 class="mb-1 font-serif text-xl font-bold sm:mb-2 sm:text-2xl lg:text-4xl">Dashboard</h1>
                <p class="text-muted-foreground text-xs sm:text-sm lg:text-base">Daftar aktivitas pengguna dalam sistem</p>
                <br />

                <!-- Stats Cards Section -->
                <div class="mb-6 grid grid-cols-1 gap-2 sm:mb-8 sm:grid-cols-2 sm:gap-6">
                    <Card class="p-2 sm:p-6">
                        <div class="flex flex-row items-center justify-between space-x-2 text-left sm:space-x-3">
                            <div>
                                <p class="text-muted-foreground text-xs font-medium">Total Users</p>
                                <p class="text-lg font-bold text-gray-900 sm:text-3xl">{{ totalUsers }}</p>
                                <p class="mt-1 text-xs" :class="usersGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ usersGrowth >= 0 ? '+' : '' }}{{ usersGrowth }}% from last month
                                </p>
                            </div>
                            <div class="flex-shrink-0 rounded-full bg-blue-100 p-2 sm:p-3">
                                <Users class="h-5 w-5 text-blue-600 sm:h-8 sm:w-8" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-2 sm:p-6">
                        <div class="flex flex-row items-center justify-between space-x-2 text-left sm:space-x-3">
                            <div>
                                <p class="text-muted-foreground text-xs font-medium">Total Subscriptions</p>
                                <p class="text-lg font-bold text-gray-900 sm:text-3xl">{{ totalSubscriptions }}</p>
                                <p class="mt-1 text-xs" :class="subscriptionsGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ subscriptionsGrowth >= 0 ? '+' : '' }}{{ subscriptionsGrowth }}% from last month
                                </p>
                            </div>
                            <div class="flex-shrink-0 rounded-full bg-green-100 p-2 sm:p-3">
                                <Receipt class="h-5 w-5 text-green-600 sm:h-8 sm:w-8" />
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Filter Section -->
                <div class="mb-4 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:gap-4">
                    <div class="relative flex-1">
                        <Search class="text-muted-foreground absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by user, action, description, or IP address..."
                            class="w-full pl-10 text-xs sm:text-sm"
                        />
                    </div>
                    <Select v-model="perPage" @update:modelValue="search">
                        <SelectTrigger class="w-full text-xs sm:w-auto sm:text-sm">
                            <SelectValue placeholder="Per page" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10 per page</SelectItem>
                            <SelectItem value="25">25 per page</SelectItem>
                            <SelectItem value="50">50 per page</SelectItem>
                            <SelectItem value="100">100 per page</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Table Section -->
                <div class="overflow-x-auto rounded-lg bg-white shadow">
                    <div class="overflow-x-auto rounded-2xl bg-white shadow-md">
                        <div class="border-b px-3 py-3 sm:px-7 sm:py-4">
                            <h2 class="font-serif text-base sm:text-lg">User Log</h2>
                            <p class="text-muted-foreground mt-1 text-xs sm:text-sm">
                                Showing {{ userLogs.data.length }} of {{ userLogs.total }} entries
                            </p>
                        </div>

                        <!-- Desktop Table -->
                        <div class="hidden lg:block">
                            <Table class="text-muted-foreground min-w-full table-fixed text-left text-sm">
                                <TableHeader>
                                    <TableRow class="text-muted-foreground bg-gray-100 text-sm">
                                        <TableHead class="w-60 whitespace-nowrap">User</TableHead>
                                        <TableHead class="w-15 whitespace-nowrap">Action</TableHead>
                                        <TableHead class="w-60 whitespace-nowrap">Description</TableHead>
                                        <TableHead class="w-36 whitespace-nowrap">IP Address</TableHead>
                                        <TableHead class="w-60 whitespace-nowrap">User Agent</TableHead>
                                        <TableHead class="w-44 whitespace-nowrap">Created At</TableHead>
                                        <TableHead class="w-44 whitespace-nowrap">Updated At</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-for="log in userLogs.data" :key="log.id">
                                        <TableRow class="cursor-pointer hover:bg-gray-100" @click="toggleRowDetail(log.id)">
                                            <TableCell class="font-medium">
                                                <div>
                                                    <div class="font-semibold">{{ log.user }}</div>
                                                    <div class="text-muted-foreground text-xs">{{ log.user_name }}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    class="rounded-full px-2 py-1 text-xs"
                                                    :class="{
                                                        'bg-green-100 text-green-800': log.action === 'Login',
                                                        'bg-blue-100 text-blue-800': log.action === 'Update',
                                                        'bg-yellow-100 text-yellow-800': log.action === 'Create',
                                                        'bg-red-100 text-red-800': log.action === 'Delete',
                                                        'text-muted-foreground bg-gray-100': !['Login', 'Update', 'Create', 'Delete'].includes(
                                                            log.action,
                                                        ),
                                                    }"
                                                >
                                                    {{ log.action }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="max-w-[12rem] truncate" :title="log.description">
                                                {{ log.description }}
                                            </TableCell>
                                            <TableCell class="max-w-[10rem] break-words whitespace-normal">
                                                {{ log.ipAddress }}
                                            </TableCell>
                                            <TableCell class="max-w-[14rem] truncate" :title="log.userAgent">
                                                {{ formatUserAgent(log.userAgent) }}
                                            </TableCell>
                                            <TableCell class="whitespace-nowrap">
                                                {{ log.createdAt }}
                                            </TableCell>
                                            <TableCell class="whitespace-nowrap">
                                                {{ log.updatedAt }}
                                            </TableCell>
                                        </TableRow>

                                        <!-- Detail Row (Expanded) -->
                                        <TableRow v-if="selectedLogId === log.id" class="bg-gray-50 text-sm">
                                            <TableCell colspan="7" class="p-4">
                                                <div class="grid gap-4 md:grid-cols-2">
                                                    <div class="space-y-1">
                                                        <div><strong>User ID:</strong> {{ log.user }}</div>
                                                        <div><strong>User Name:</strong> {{ log.user_name }}</div>
                                                        <div><strong>Action:</strong> {{ log.action }}</div>
                                                        <div><strong>Description:</strong> {{ log.description }}</div>
                                                        <div><strong>IP Address:</strong> {{ log.ipAddress }}</div>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <div><strong>User Agent:</strong></div>
                                                        <div class="rounded bg-gray-100 px-2 py-1 text-gray-700">
                                                            {{ log.userAgent }}
                                                        </div>
                                                        <div><strong>Created At:</strong> {{ log.createdAt }}</div>
                                                        <div><strong>Updated At:</strong> {{ log.updatedAt }}</div>
                                                    </div>
                                                </div>

                                                <div class="mt-4">
                                                    <div class="mb-1 font-semibold text-red-700">Old Values:</div>
                                                    <Card class="bg-red-50">
                                                        <CardContent class="text-[10px]" text-red-900 whitespace-pre-wrap p-2>
                                                            <code>
                                                                {{ formatJsonValues(log.oldValues) }}
                                                            </code>
                                                        </CardContent>
                                                    </Card>
                                                </div>

                                                <div class="mt-4">
                                                    <div class="mb-1 font-semibold text-green-700">New Values:</div>
                                                    <Card class="bg-red-50">
                                                        <CardContent class="p-2 text-[10px] whitespace-pre-wrap text-green-900">
                                                            <code>
                                                                {{ formatJsonValues(log.newValues) }}
                                                            </code>
                                                        </CardContent>
                                                    </Card>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Mobile Version: Card layout -->
                        <div class="block lg:hidden">
                            <div class="divide-y">
                                <div v-for="log in userLogs.data" :key="log.id" class="flex flex-col gap-1 px-2 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="truncate text-xs font-semibold">{{ log.user }}</div>
                                        <Badge
                                            class="ml-2 rounded-full px-2 py-1 text-xs"
                                            :class="{
                                                'bg-green-100 text-green-800': log.action === 'Login',
                                                'bg-blue-100 text-blue-800': log.action === 'Update',
                                                'bg-yellow-100 text-yellow-800': log.action === 'Create',
                                                'bg-red-100 text-red-800': log.action === 'Delete',
                                                'text-muted-foreground bg-gray-100': !['Login', 'Update', 'Create', 'Delete'].includes(log.action),
                                            }"
                                        >
                                            {{ log.action }}
                                        </Badge>
                                    </div>
                                    <div class="text-muted-foreground truncate text-xs">{{ log.user_name }}</div>
                                    <div class="mt-1 truncate text-xs" :title="log.description">
                                        <Badge class="font-medium">Desc:</Badge> {{ log.description }}
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-x-2 text-xs">
                                        <Badge class="truncate"><Badge class="font-medium">IP:</Badge> {{ log.ipAddress }}</Badge>
                                        <Badge class="truncate"><Badge class="font-medium">UA:</Badge> {{ formatUserAgent(log.userAgent) }}</Badge>
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-x-2 text-xs">
                                        <Badge><Badge class="font-medium">Created:</Badge> {{ log.createdAt }}</Badge>
                                        <Badge><Badge class="font-medium">Updated:</Badge> {{ log.updatedAt }}</Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Section -->
                <div class="mt-4 flex flex-col items-center justify-between gap-2 sm:mt-6 sm:flex-row">
                    <div class="text-muted-foreground text-xs sm:text-sm">
                        Page {{ userLogs.current_page }} of {{ userLogs.last_page }} ({{ userLogs.total }} total entries)
                    </div>
                    <div class="flex space-x-2">
                        <Button variant="outline" size="sm" :disabled="userLogs.current_page === 1" @click="goToPage(userLogs.current_page - 1)">
                            <ChevronLeft class="mr-1 h-4 w-4" />
                            <Badge class="xs:inline hidden">Previous</Badge>
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="userLogs.current_page === userLogs.last_page"
                            @click="goToPage(userLogs.current_page + 1)"
                        >
                            <Badge class="xs:inline hidden">Next</Badge>
                            <ChevronRight class="ml-1 h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- No data fallback -->
                <div v-if="userLogs.data.length === 0" class="py-8 text-center sm:py-12">
                    <p class="text-muted-foreground text-xs sm:text-base">Tidak ada log aktivitas pengguna</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
