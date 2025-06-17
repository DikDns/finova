<script setup lang="ts">
import AdminSideBarLayout from '@/components/admin/AdminSideBarLayout.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Head, router } from '@inertiajs/vue3';
import { Banknote, ChevronLeft, ChevronRight, CreditCard, PiggyBank, Search, TrendingUp, Wallet } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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
        loan: number;
    };
    availableTypes?: string[];
}>();

// State variables
const searchQuery = ref(props.filters.search || '');
const perPage = ref(String(props.filters.per_page || 10));
const typeFilter = ref(props.filters.type || '');

// Computed property for corrected total balance (loans should be negative)
const correctedTotalBalance = computed(() => {
    return props.accounts.data.reduce((total, account) => {
        // For loan accounts, treat balance as negative in the total
        if (account.type.toLowerCase() === 'loan') {
            return total - Math.abs(account.balance);
        }
        // For credit accounts, if balance is positive, it means debt (negative for net worth)
        if (account.type.toLowerCase().includes('credit')) {
            return total - Math.abs(account.balance);
        }
        // For other account types (cash, savings, investment), balance is positive
        return total + account.balance;
    }, 0);
});

// Computed property for corrected average balance
const correctedAverageBalance = computed(() => {
    return props.totalAccounts > 0 ? correctedTotalBalance.value / props.totalAccounts : 0;
});

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

// Watch type filter changes - Fixed to properly trigger search
watch(typeFilter, (newValue, oldValue) => {
    // Only search if the value actually changed
    if (newValue !== oldValue) {
        search();
    }
});

// Watch per page changes
watch(perPage, () => {
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
        case 'loan':
            return 'bg-orange-100 text-orange-800';
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
        case 'loan':
            return Banknote;
        default:
            return Wallet;
    }
};

// Get available types for dropdown (fallback if not provided from backend)
// const getAvailableTypes = () => {
//     if (props.availableTypes && props.availableTypes.length > 0) {
//         return props.availableTypes;
//     }
//     // Fallback types
//     return ['cash', 'savings', 'credit', 'investment', 'loan'];
// };

// Helper function to display balance with proper sign for different account types
const displayBalance = (account: Account) => {
    const balance = account.balance;
    const type = account.type.toLowerCase();

    // For loan accounts, display as debt (positive numbers represent money owed)
    if (type === 'loan') {
        return {
            amount: Math.abs(balance),
            isPositive: false,
            label: 'Debt',
        };
    }

    // For credit accounts, positive balance means debt
    if (type.includes('credit')) {
        return {
            amount: Math.abs(balance),
            isPositive: balance <= 0, // Negative balance in credit means credit available
            label: balance > 0 ? 'Debt' : 'Available Credit',
        };
    }

    // For other accounts (cash, savings, investment)
    return {
        amount: Math.abs(balance),
        isPositive: balance >= 0,
        label: 'Balance',
    };
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
                <h1 class="mb-1 font-serif text-xl font-bold sm:mb-2 sm:text-2xl lg:text-4xl">Manajemen Akun</h1>
                <p class="text-muted-foreground text-xs sm:text-sm lg:text-base">Kelola dan pantau semua akun pengguna dalam sistem</p>
                <br />

                <!-- Stats Cards Section -->
                <div class="mb-6 grid grid-cols-1 gap-2 sm:mb-8 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                    <Card class="p-2 sm:p-6">
                        <div class="flex flex-row items-center justify-between space-x-2 text-left sm:space-x-3">
                            <div>
                                <p class="text-muted-foreground text-xs font-medium">Total Akun</p>
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
                                <p class="text-muted-foreground text-xs font-medium">Saldo Bersih</p>
                                <p class="text-lg font-bold sm:text-2xl" :class="correctedTotalBalance >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(correctedTotalBalance) }}
                                </p>
                                <p class="text-xs text-gray-500">Raw Total: {{ formatCurrency(totalBalance) }}</p>
                            </div>
                            <div class="flex-shrink-0 rounded-full bg-green-100 p-2 sm:p-3">
                                <PiggyBank class="h-5 w-5 text-green-600 sm:h-8 sm:w-8" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-2 sm:p-6">
                        <div class="flex flex-row items-center justify-between space-x-2 text-left sm:space-x-3">
                            <div>
                                <p class="text-muted-foreground text-xs font-medium">Rata-Rata Saldo Bersih</p>
                                <p class="text-lg font-bold sm:text-2xl" :class="correctedAverageBalance >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(correctedAverageBalance) }}
                                </p>
                                <p class="text-xs text-gray-500">Raw rerata: {{ formatCurrency(averageBalance) }}</p>
                            </div>
                            <div class="flex-shrink-0 rounded-full bg-purple-100 p-2 sm:p-3">
                                <TrendingUp class="h-5 w-5 text-purple-600 sm:h-8 sm:w-8" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-2 sm:p-6">
                        <div class="space-y-2">
                            <p class="text-muted-foreground text-xs font-medium">Tipe Akun</p>
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span>Uang:</span>
                                    <span class="font-medium">{{ accountsByType.cash }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Tabungan:</span>
                                    <span class="font-medium">{{ accountsByType.savings }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Kredit:</span>
                                    <span class="font-medium">{{ accountsByType.credit }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Investasi:</span>
                                    <span class="font-medium">{{ accountsByType.investment }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span>Pinjaman:</span>
                                    <span class="font-medium">{{ accountsByType.loan }}</span>
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
                            placeholder="Cari berdasarkan nama akun, nama pengguna, email, atau budget..."
                            class="w-full pl-10 text-xs sm:text-sm"
                        />
                    </div>
                    <Select v-model="perPage">
                        <SelectTrigger class="w-full text-xs sm:w-auto sm:text-sm">
                            <SelectValue placeholder="Per halaman" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10 per halaman</SelectItem>
                            <SelectItem value="25">25 per halaman</SelectItem>
                            <SelectItem value="50">50 per halaman</SelectItem>
                            <SelectItem value="100">100 per halaman</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Table Section -->
                <div class="overflow-x-auto rounded-lg bg-white shadow">
                    <div class="overflow-x-auto rounded-2xl bg-white shadow-md">
                        <div class="border-b px-3 py-3 sm:px-7 sm:py-4">
                            <h2 class="font-serif text-base sm:text-lg">Akun</h2>
                            <p class="text-muted-foreground mt-1 text-xs sm:text-sm">
                                Menampilkan {{ accounts.data.length }} dari {{ accounts.total }} akun
                                <span v-if="typeFilter" class="ml-2 font-medium">
                                    (Difilter berdasarkan: {{ typeFilter.charAt(0).toUpperCase() + typeFilter.slice(1).replace('_', ' ') }})
                                </span>
                            </p>
                        </div>

                        <!-- Desktop Table -->
                        <div class="hidden lg:block">
                            <Table class="text-muted-foreground min-w-full table-fixed text-left text-sm">
                                <TableHeader>
                                    <TableRow class="text-muted-foreground bg-gray-100 text-sm">
                                        <TableHead class="w-48 whitespace-nowrap">Nama Akun</TableHead>
                                        <TableHead class="w-32 whitespace-nowrap">Jenis</TableHead>
                                        <TableHead class="w-48 whitespace-nowrap">Pengguna</TableHead>
                                        <TableHead class="w-48 whitespace-nowrap">Anggaran</TableHead>
                                        <TableHead class="w-36 whitespace-nowrap">Neraca</TableHead>
                                        <TableHead class="w-32 whitespace-nowrap">Bunga</TableHead>
                                        <TableHead class="w-40 whitespace-nowrap">Pembayaran minimum</TableHead>
                                        <TableHead class="w-40 whitespace-nowrap">Dibuat pada</TableHead>
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
                                                <div>
                                                    <span :class="displayBalance(account).isPositive ? 'text-green-600' : 'text-red-600'">
                                                        {{ formatCurrency(displayBalance(account).amount) }}
                                                    </span>
                                                    <div class="text-xs text-gray-500">{{ displayBalance(account).label }}</div>
                                                </div>
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
                                                        <div><strong>ID Rekening:</strong> {{ account.id }}</div>
                                                        <div><strong>ID Anggaran:</strong> {{ account.budget_id }}</div>
                                                        <div><strong>Nama Akun:</strong> {{ account.name }}</div>
                                                        <div>
                                                            <strong>Tipe Akun:</strong>
                                                            <Badge :class="getAccountTypeBadgeClass(account.type)" class="ml-2">
                                                                {{ account.type }}
                                                            </Badge>
                                                        </div>
                                                        <div><strong>Pengguna:</strong> {{ account.user_name }} ({{ account.user_email }})</div>
                                                        <div><strong>Anggaran:</strong> {{ account.budget_name }}</div>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <div>
                                                            <strong>{{ displayBalance(account).label }}:</strong>
                                                            <span
                                                                :class="displayBalance(account).isPositive ? 'text-green-600' : 'text-red-600'"
                                                                class="ml-2 font-semibold"
                                                            >
                                                                {{ formatCurrency(displayBalance(account).amount) }}
                                                            </span>
                                                            <div class="mt-1 text-xs text-gray-500">Saldo: {{ formatCurrency(account.balance) }}</div>
                                                        </div>
                                                        <div>
                                                            <strong>Suku Bunga:</strong>
                                                            {{ account.interest > 0 ? formatPercentage(account.interest) : 'No Interest' }}
                                                        </div>
                                                        <div>
                                                            <strong>Pembayaran Bulanan Minimum:</strong>
                                                            {{
                                                                account.minimum_payment_monthly > 0
                                                                    ? formatCurrency(account.minimum_payment_monthly)
                                                                    : 'No Minimum Payment'
                                                            }}
                                                        </div>
                                                        <div><strong>Dibuat pada:</strong> {{ account.createdAt }}</div>
                                                        <div><strong>Diperbarui pada:</strong> {{ account.updatedAt }}</div>
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

                                    <div class="text-xs"><Badge class="text-xs">Anggaran:</Badge> {{ account.budget_name }}</div>

                                    <div class="flex items-center justify-between">
                                        <div class="text-xs">
                                            <Badge class="font-medium">{{ displayBalance(account).label }}:</Badge>
                                            <span
                                                :class="displayBalance(account).isPositive ? 'text-green-600' : 'text-red-600'"
                                                class="ml-1 font-semibold"
                                            >
                                                {{ formatCurrency(displayBalance(account).amount) }}
                                            </span>
                                        </div>
                                        <div class="text-muted-foreground text-xs">
                                            {{ account.createdAt }}
                                        </div>
                                    </div>

                                    <div v-if="account.interest > 0 || account.minimum_payment_monthly > 0" class="flex flex-wrap gap-2 text-xs">
                                        <Badge v-if="account.interest > 0"> Bunga: {{ formatPercentage(account.interest) }} </Badge>
                                        <Badge v-if="account.minimum_payment_monthly > 0">
                                            Minimum pembayaran: {{ formatCurrency(account.minimum_payment_monthly) }}
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
                        Halaman {{ accounts.current_page }} dari {{ accounts.last_page }} ({{ accounts.total }} total akun)
                    </div>
                    <div class="flex space-x-2">
                        <Button variant="outline" size="sm" :disabled="accounts.current_page === 1" @click="goToPage(accounts.current_page - 1)">
                            <ChevronLeft class="mr-1 h-4 w-4" />
                            <span class="xs:inline hidden">Sebelumnya</span>
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="accounts.current_page === accounts.last_page"
                            @click="goToPage(accounts.current_page + 1)"
                        >
                            <span class="xs:inline hidden">Selanjutnya</span>
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
