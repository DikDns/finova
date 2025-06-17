<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface SubscriptionLogEntry {
    id: string;
    user: string;
    invoice: string;
    payment_method: string;
    start_date: string;
    end_date: string;
    created_at: string;
    updated_at: string;
}

interface SubscriptionLogsData {
    data: SubscriptionLogEntry[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    subscriptionLogs: SubscriptionLogsData;
    filters: {
        search?: string;
        per_page: number;
    };
}>();

const searchQuery = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);

const search = () => {
    router.get(
        route('admin.subscription'),
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

const goToPage = (page: number) => {
    router.get(
        route('admin.subscription'),
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

// Watch for search input changes and debounce
let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        search();
    }, 500);
});
</script>

<template>
    <Head title="Admin Subscription Log" />

    <AdminLayout>
        <!-- Main Content -->
        <main class="px-4 pt-16 pb-10 sm:px-6 lg:px-10">
            <!-- Header Section -->
            <h1 class="mb-1 font-serif text-xl font-bold sm:mb-2 sm:text-2xl lg:text-4xl">Subscription Log</h1>
            <p class="text-muted-foreground text-xs sm:text-sm lg:text-base">Daftar aktivitas subscription pengguna dalam sistem</p>

            <!-- Search and Filter Section -->
            <div class="mt-6 mb-6 flex flex-col gap-4 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                    <Input v-model="searchQuery" placeholder="Search by user, invoice, payment method, start date or end date..." class="pl-10" />
                </div>
                <select
                    v-model="perPage"
                    @change="search"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>

            <!-- Table -->
            <Table>
                <TableHeader>
                    <TableRow class="bg-gray-100 text-sm text-gray-600">
                        <TableHead class="w-60 whitespace-nowrap">User</TableHead>
                        <TableHead class="w-15 whitespace-nowrap">Invoice</TableHead>
                        <TableHead class="w-60 whitespace-nowrap">Payment Method</TableHead>
                        <TableHead class="w-36 whitespace-nowrap">Start Date</TableHead>
                        <TableHead class="w-60 whitespace-nowrap">End Date</TableHead>
                        <TableHead class="w-44 whitespace-nowrap">Created At</TableHead>
                        <TableHead class="w-44 whitespace-nowrap">Updated At</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-for="log in subscriptionLogs.data" :key="log.id">
                        <TableRow class="hover:bg-gray-100">
                            <TableCell class="font-medium">{{ log.user }}</TableCell>
                            <TableCell>{{ log.invoice }}</TableCell>
                            <TableCell>{{ log.payment_method }}</TableCell>
                            <TableCell>{{ log.start_date }}</TableCell>
                            <TableCell>{{ log.end_date }}</TableCell>
                            <TableCell>{{ log.created_at }}</TableCell>
                            <TableCell>{{ log.updated_at }}</TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>

            <!-- Pagination & Empty State -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Page {{ subscriptionLogs.current_page }} of {{ subscriptionLogs.last_page }} ({{ subscriptionLogs.total }} total entries)
                </div>
                <div class="flex space-x-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="subscriptionLogs.current_page === 1"
                        @click="goToPage(subscriptionLogs.current_page - 1)"
                    >
                        <ChevronLeft class="mr-1 h-4 w-4" /> Previous
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="subscriptionLogs.current_page === subscriptionLogs.last_page"
                        @click="goToPage(subscriptionLogs.current_page + 1)"
                    >
                        Next <ChevronRight class="ml-1 h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div v-if="subscriptionLogs.data.length === 0" class="py-12 text-center">
                <p class="text-muted-foreground">Tidak ada log aktivitas subscription pengguna</p>
            </div>
        </main>
    </AdminLayout>
</template>
