<script setup lang="ts">
import AdminSideBarLayout from '@/components/admin/AdminSideBarLayout.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, CreditCard, PiggyBank, Search, TrendingUp, Wallet } from 'lucide-vue-next';
import { ref, watch } from 'vue';

// Define interfaces for type safety
interface Account {
    id: string;
    budget_id: string;
    budget_name: string;
    user_name: string;
    user_email: string;
    name: string;
    type: string;
    interest: number;
    minimum_payment_monthly: number;
    balance: number;
    createdAt: string;
    updatedAt: string;
}

interface AccountsData {
    data: Account[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

// Props from server
const props = defineProps<{
    accounts: AccountsData;
    filters: {
        search?: string;
        per_page: number;
        type?: string;
    };
    totalAccounts: number;
    totalBalance: number;
    averageBalance: number;
    accountsByType: {
        cash: number;
        savings: number;
        credit: number;
        investment: number;
    };
}>();

// State variables
const searchQuery = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);
const typeFilter = ref(props.filters.type || '');

// Perform a search and update the view
const search = () => {
    router.get(
        route('admin.accounts'),
        {
            search: searchQuery.value,
            per_page: perPage.value,
            type: typeFilter.value,
            page: 1,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Go to a specific page
const goToPage = (page: number) => {
    router.get(
        route('admin.accounts'),
        {
            search: searchQuery.value,
            per_page: perPage.value,
            type: typeFilter.value,
            page: page,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Format currency
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Format percentage
const formatPercentage = (value: number) => {
    return `${value.toFixed(2)}%`;
};

// Debounce the search input
let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        search();
    }, 500);
});

// Watch type filter changes
watch(typeFilter, () => {
    search();
});

// Track which account row is expanded
const selectedAccountId = ref<string | null>(null);

// Toggle the expanded detail row
const toggleRowDetail = (id: string) => {
    selectedAccountId.value = selectedAccountId.value === id ? null : id;
};

// Get account type badge color
const getAccountTypeBadgeClass = (type: string) => {
    switch (type.toLowerCase()) {
        case 'cash':
            return 'bg-green-100 text-green-800';
        case 'savings':
            return 'bg-blue-100 text-blue-800';
        case 'credit':
        case 'credit_card':
            return 'bg-red-100 text-red-800';
        case 'investment':
            return 'bg-purple-100 text-purple-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// Get account type icon
const getAccountTypeIcon = (type: string) => {
    switch (type.toLowerCase()) {
        case 'cash':
            return Wallet;
        case 'savings':
            return PiggyBank;
        case 'credit':
        case 'credit_card':
            return CreditCard;
        case 'investment':
            return TrendingUp;
        default:
            return Wallet;
    }
};
</script>

<template>
    <div class="flex min-h-screen flex-col lg:flex-row">
        <Head title="Finova - Admin - Accounts" />
        <AdminSideBarLayout class="hidden lg:block" />

        <div class="flex-1 px-2 pt-8 pb-10 sm:px-4 lg:px-13">
            <div class="mx-auto w-full max-w-7xl">
                <!-- Header Section -->
                <div class="mb-4 sm:mb-6"></div>
                <h1 class="mb-1 font-serif text-xl font-bold sm:mb-2 sm:text-2xl lg:text-4xl">Accounts Management</h1>
                <p class="text-muted-foreground text-xs sm:text-sm lg:text-base">Kelola dan pantau semua akun pengguna dalam sistem</p>
                <br />

                <!-- Stats Cards Section -->
                <div class="mb-6 grid grid-cols-1 gap-2 sm:mb-8 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                    <Card class="p-2 sm:p-6">
                        <div class="flex flex-row items-center justify-between space-x-2 text-left sm:space-x-3">
                            <div>
                                <p class="text-muted-foreground text-xs font-medium">Total Accounts</p>
                                <p class="text-lg font-bold text-gray-900 sm:text-3xl">{{ totalAccounts }}</p>
                            </div>
                            <div class="flex-shrink-0 rounded-full bg-blue-100 p-2 sm:p-3">
                                <Wallet class="h-5 w-5 text-blue-600 sm:h-8 sm:w-8" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-2 sm:p-6">
                        <div class="flex flex-row items-center justify-between space-x-2 text-left sm:space-x-3">
                            <div>
                                <p class="text-muted-foreground text-xs font-medium">Total Balance</p>
                                <p class="text-lg font-bold text-gray-900 sm:text-2xl">{{ formatCurrency(totalBalance) }}</p>
                            </div>
                            <div class="flex-shrink-0 rounded-full bg-green-100 p-2 sm:p-3">
                                <PiggyBank class="h-5 w-5 text-green-600 sm:h-8 sm:w-8" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-2 sm:p-6">
                        <div class="flex flex-row items-center justify-between space-x-2 text-left sm:space-x-3">
                            <div>
                                <p class="text-muted-foreground text-xs font-medium">Average Balance</p>
                                <p class="text-lg font-bold text-gray-900 sm:text-2xl">{{ formatCurrency(averageBalance) }}</p>
                            </div>
                            <div class="flex-shrink-0 rounded-full bg-purple-100 p-2 sm:p-3">
                                <TrendingUp class="h-5 w-5 text-purple-600 sm:h-8 sm:w-8" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-2 sm:p-6">
                        <div class="space-y-2">
                            <p class="text-muted-foreground text-xs font-medium">Account Types</p>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span>Cash:</span>
                                    <span class="font-medium">{{ accountsByType.cash }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Savings:</span>
                                    <span class="font-medium">{{ accountsByType.savings }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Credit:</span>
                                    <span class="font-medium">{{ accountsByType.credit }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Investment:</span>
                                    <span class="font-medium">{{ accountsByType.investment }}</span>
                                </div>
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
                            placeholder="Search by account name, user name, email, or budget..."
                            class="w-full pl-10 text-xs sm:text-sm"
                        />
                    </div>
                    <Select v-model="typeFilter">
                        <SelectTrigger class="w-full text-xs sm:w-auto sm:text-sm">
                            <SelectValue placeholder="All Types" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All Types</SelectItem>
                            <SelectItem value="cash">Cash</SelectItem>
                            <SelectItem value="savings">Savings</SelectItem>
                            <SelectItem value="credit">Credit</SelectItem>
                            <SelectItem value="investment">Investment</SelectItem>
                        </SelectContent>
                    </Select>
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
                            <h2 class="font-serif text-base sm:text-lg">Accounts</h2>
                            <p class="text-muted-foreground mt-1 text-xs sm:text-sm">
                                Showing {{ accounts.data.length }} of {{ accounts.total }} accounts
                            </p>
                        </div>

                        <!-- Desktop Table -->
                        <div class="hidden lg:block">
                            <Table class="text-muted-foreground min-w-full table-fixed text-left text-sm">
                                <TableHeader>
                                    <TableRow class="text-muted-foreground bg-gray-100 text-sm">
                                        <TableHead class="w-48 whitespace-nowrap">Account Name</TableHead>
                                        <TableHead class="w-32 whitespace-nowrap">Type</TableHead>
                                        <TableHead class="w-48 whitespace-nowrap">User</TableHead>
                                        <TableHead class="w-48 whitespace-nowrap">Budget</TableHead>
                                        <TableHead class="w-36 whitespace-nowrap">Balance</TableHead>
                                        <TableHead class="w-32 whitespace-nowrap">Interest</TableHead>
                                        <TableHead class="w-40 whitespace-nowrap">Min Payment</TableHead>
                                        <TableHead class="w-40 whitespace-nowrap">Created At</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-for="account in accounts.data" :key="account.id">
                                        <TableRow class="cursor-pointer hover:bg-gray-100" @click="toggleRowDetail(account.id)">
                                            <TableCell class="font-medium">
                                                <div class="flex items-center space-x-2">
                                                    <component :is="getAccountTypeIcon(account.type)" class="text-muted-foreground h-4 w-4" />
                                                    <span>{{ account.name }}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge class="rounded-full px-2 py-1 text-xs" :class="getAccountTypeBadgeClass(account.type)">
                                                    {{ account.type }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div>
                                                    <div class="text-xs font-semibold">{{ account.user_name }}</div>
                                                    <div class="text-muted-foreground text-xs">{{ account.user_email }}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell class="max-w-[12rem] truncate" :title="account.budget_name">
                                                {{ account.budget_name }}
                                            </TableCell>
                                            <TableCell class="font-semibold">
                                                <span :class="account.balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                                    {{ formatCurrency(account.balance) }}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                {{ account.interest > 0 ? formatPercentage(account.interest) : '-' }}
                                            </TableCell>
                                            <TableCell>
                                                {{ account.minimum_payment_monthly > 0 ? formatCurrency(account.minimum_payment_monthly) : '-' }}
                                            </TableCell>
                                            <TableCell class="text-xs whitespace-nowrap">
                                                {{ account.createdAt }}
                                            </TableCell>
                                        </TableRow>

                                        <!-- Detail Row (Expanded) -->
                                        <TableRow v-if="selectedAccountId === account.id" class="bg-gray-50 text-sm">
                                            <TableCell colspan="8" class="p-4">
                                                <div class="grid gap-4 md:grid-cols-2">
                                                    <div class="space-y-2">
                                                        <div><strong>Account ID:</strong> {{ account.id }}</div>
                                                        <div><strong>Budget ID:</strong> {{ account.budget_id }}</div>
                                                        <div><strong>Account Name:</strong> {{ account.name }}</div>
                                                        <div>
                                                            <strong>Account Type:</strong>
                                                            <Badge :class="getAccountTypeBadgeClass(account.type)" class="ml-2">
                                                                {{ account.type }}
                                                            </Badge>
                                                        </div>
                                                        <div><strong>User:</strong> {{ account.user_name }} ({{ account.user_email }})</div>
                                                        <div><strong>Budget:</strong> {{ account.budget_name }}</div>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <div>
                                                            <strong>Balance:</strong>
                                                            <span
                                                                :class="account.balance >= 0 ? 'text-green-600' : 'text-red-600'"
                                                                class="font-semibold"
                                                            >
                                                                {{ formatCurrency(account.balance) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <strong>Interest Rate:</strong>
                                                            {{ account.interest > 0 ? formatPercentage(account.interest) : 'No Interest' }}
                                                        </div>
                                                        <div>
                                                            <strong>Minimum Monthly Payment:</strong>
                                                            {{
                                                                account.minimum_payment_monthly > 0
                                                                    ? formatCurrency(account.minimum_payment_monthly)
                                                                    : 'No Minimum Payment'
                                                            }}
                                                        </div>
                                                        <div><strong>Created At:</strong> {{ account.createdAt }}</div>
                                                        <div><strong>Updated At:</strong> {{ account.updatedAt }}</div>
                                                    </div>
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
                                <div v-for="account in accounts.data" :key="account.id" class="flex flex-col gap-2 px-2 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <component :is="getAccountTypeIcon(account.type)" class="text-muted-foreground h-4 w-4" />
                                            <span class="text-sm font-semibold">{{ account.name }}</span>
                                        </div>
                                        <Badge class="rounded-full px-2 py-1 text-xs" :class="getAccountTypeBadgeClass(account.type)">
                                            {{ account.type }}
                                        </Badge>
                                    </div>

                                    <div class="text-xs">
                                        <div class="font-medium">{{ account.user_name }}</div>
                                        <div class="text-muted-foreground">{{ account.user_email }}</div>
                                    </div>

                                    <div class="text-xs"><Badge class="text-xs">Budget:</Badge> {{ account.budget_name }}</div>

                                    <div class="flex items-center justify-between">
                                        <div class="text-xs">
                                            <Badge class="font-medium">Balance:</Badge>
                                            <span :class="account.balance >= 0 ? 'text-green-600' : 'text-red-600'" class="ml-1 font-semibold">
                                                {{ formatCurrency(account.balance) }}
                                            </span>
                                        </div>
                                        <div class="text-muted-foreground text-xs">
                                            {{ account.createdAt }}
                                        </div>
                                    </div>

                                    <div v-if="account.interest > 0 || account.minimum_payment_monthly > 0" class="flex flex-wrap gap-2 text-xs">
                                        <Badge v-if="account.interest > 0"> Interest: {{ formatPercentage(account.interest) }} </Badge>
                                        <Badge v-if="account.minimum_payment_monthly > 0">
                                            Min Payment: {{ formatCurrency(account.minimum_payment_monthly) }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Section -->
                <div class="mt-4 flex flex-col items-center justify-between gap-2 sm:mt-6 sm:flex-row">
                    <div class="text-muted-foreground text-xs sm:text-sm">
                        Page {{ accounts.current_page }} of {{ accounts.last_page }} ({{ accounts.total }} total accounts)
                    </div>
                    <div class="flex space-x-2">
                        <Button variant="outline" size="sm" :disabled="accounts.current_page === 1" @click="goToPage(accounts.current_page - 1)">
                            <ChevronLeft class="mr-1 h-4 w-4" />
                            <span class="xs:inline hidden">Previous</span>
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="accounts.current_page === accounts.last_page"
                            @click="goToPage(accounts.current_page + 1)"
                        >
                            <span class="xs:inline hidden">Next</span>
                            <ChevronRight class="ml-1 h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- No data fallback -->
                <div v-if="accounts.data.length === 0" class="py-8 text-center sm:py-12">
                    <p class="text-muted-foreground text-xs sm:text-base">Tidak ada data akun ditemukan</p>
                </div>
            </div>
        </div>
    </div>
</template>
