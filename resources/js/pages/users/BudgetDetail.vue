<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Category {
    id: string;
    name: string;
    icon?: string;
    allocated: number;
    spent: number;
    target: number;
}

interface CategoryGroup {
    id: string;
    name: string;
    categories: Category[];
    totalAllocated: number;
    totalSpent: number;
    totalTarget: number;
}

interface MonthlyBudget {
    id: string;
    month: string;
    total_income: number;
    total_assigned: number;
    total_activity: number;
    total_available: number;
    category_groups?: CategoryGroup[];
}

interface Budget {
    id: string;
    name: string;
    description: string;
    amount: number;
    currency_code: string;
    created_at: string;
    updated_at: string;
    monthly_budgets?: MonthlyBudget[];
}

interface Props {
    budget: Budget;
}

const props = defineProps<Props>();

// Active tab state
const activeTab = ref('semua');

// Active group state (for accordion)
const activeGroups = ref<Set<string>>(new Set());

// Current month state
const currentMonth = ref(new Date());

// Format currency
const formatCurrency = (amount: number, currencyCode = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Format month
const formattedMonth = computed(() => {
    return currentMonth.value.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
});

// Navigate to previous month
const goToPrevMonth = () => {
    const newDate = new Date(currentMonth.value);
    newDate.setMonth(newDate.getMonth() - 1);
    currentMonth.value = newDate;
};

// Navigate to next month
const goToNextMonth = () => {
    const newDate = new Date(currentMonth.value);
    newDate.setMonth(newDate.getMonth() + 1);
    currentMonth.value = newDate;
};

// Toggle group accordion
const toggleGroup = (groupId: string) => {
    if (activeGroups.value.has(groupId)) {
        activeGroups.value.delete(groupId);
    } else {
        activeGroups.value.add(groupId);
    }
};

// Check if group is active
const isGroupActive = (groupId: string) => {
    return activeGroups.value.has(groupId);
};

// Set active tab
const setActiveTab = (tab: string) => {
    activeTab.value = tab;
};

// Breadcrumbs for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.budget.name,
        href: route('budget', props.budget.id),
    },
];

// Mock data for demonstration (will be replaced with actual data from props)
const mockCategoryGroups: CategoryGroup[] = [
    {
        id: 'tagihan',
        name: 'Tagihan',
        totalAllocated: 450000,
        totalSpent: -350000,
        totalTarget: 100000,
        categories: [
            { id: '1', name: 'KPR', icon: '🏠', allocated: 450000, spent: -350000, target: 100000 },
            { id: '2', name: 'Listrik', icon: '⚡', allocated: 450000, spent: -350000, target: 100000 },
            { id: '3', name: 'Sampah', icon: '🗑️', allocated: 450000, spent: -350000, target: 100000 },
            { id: '4', name: 'Air', icon: '💧', allocated: 450000, spent: -350000, target: 100000 },
            { id: '5', name: 'Internet', icon: '📶', allocated: 450000, spent: -350000, target: 100000 },
        ],
    },
    {
        id: 'kebutuhan',
        name: 'Kebutuhan Sehari-hari',
        totalAllocated: 1500000,
        totalSpent: -1200000,
        totalTarget: 300000,
        categories: [
            { id: '6', name: 'Makanan', icon: '🍔', allocated: 800000, spent: -750000, target: 50000 },
            { id: '7', name: 'Transportasi', icon: '🚗', allocated: 400000, spent: -300000, target: 100000 },
            { id: '8', name: 'Kesehatan', icon: '💊', allocated: 300000, spent: -150000, target: 150000 },
        ],
    },
];

// Available balance (to be allocated)
const availableBalance = ref(2000000);
</script>

<template>
    <Head :title="`${props.budget.name} | Budget Detail`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="flex flex-col gap-6">
                <!-- Header with month selector -->
                <div class="flex items-center justify-between">
                    <h1 class="font-serif text-2xl font-semibold tracking-tight">{{ props.budget.name }}</h1>

                    <div class="flex items-center space-x-2">
                        <Button variant="outline" size="icon" @click="goToPrevMonth">
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="text-sm font-medium">{{ formattedMonth }}</span>
                        <Button variant="outline" size="icon" @click="goToNextMonth">
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Balance card -->
                <div class="bg-card rounded-lg border p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                <span class="text-xl font-semibold">{{ formatCurrency(availableBalance) }}</span>
                            </div>
                            <p class="text-muted-foreground text-sm">Siap dialokasikan</p>
                        </div>
                        <Button>Alokasikan</Button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b">
                    <div class="flex space-x-6">
                        <button
                            class="border-primary hover:text-primary data-[active=true]:text-primary text-sm font-medium transition-all data-[active=true]:border-b-2"
                            :data-active="activeTab === 'semua'"
                            @click="setActiveTab('semua')"
                        >
                            Semua
                        </button>
                        <button
                            class="border-primary hover:text-primary data-[active=true]:text-primary text-sm font-medium transition-all data-[active=true]:border-b-2"
                            :data-active="activeTab === 'kekurangan'"
                            @click="setActiveTab('kekurangan')"
                        >
                            Kekurangan Dana
                        </button>
                        <button
                            class="border-primary hover:text-primary data-[active=true]:text-primary text-sm font-medium transition-all data-[active=true]:border-b-2"
                            :data-active="activeTab === 'kelebihan'"
                            @click="setActiveTab('kelebihan')"
                        >
                            Kelebihan Dana
                        </button>
                        <button
                            class="border-primary hover:text-primary data-[active=true]:text-primary text-sm font-medium transition-all data-[active=true]:border-b-2"
                            :data-active="activeTab === 'dinonaktifkan'"
                            @click="setActiveTab('dinonaktifkan')"
                        >
                            Dinonaktifkan
                        </button>
                    </div>
                </div>

                <!-- Budget table -->
                <div class="rounded-lg border shadow-sm">
                    <div class="p-4">
                        <Button variant="outline" class="flex items-center gap-2">
                            <Plus class="h-4 w-4" />
                            <span>Tambah Kategori</span>
                        </Button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-muted/50 border-b">
                                    <th class="p-3 text-left font-medium">Kategori</th>
                                    <th class="p-3 text-left font-medium">Dialokasikan</th>
                                    <th class="p-3 text-left font-medium">Transaksi</th>
                                    <th class="p-3 text-left font-medium">Target</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Category groups with accordion -->
                                <template v-for="group in mockCategoryGroups" :key="group.id">
                                    <!-- Group header row -->
                                    <tr class="hover:bg-muted/50 cursor-pointer border-b transition-colors" @click="toggleGroup(group.id)">
                                        <td class="p-3" colspan="4">
                                            <div class="flex items-center gap-2">
                                                <ChevronRight
                                                    class="h-4 w-4 transition-transform"
                                                    :class="{ 'rotate-90': isGroupActive(group.id) }"
                                                />
                                                {{ group.name }}
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Group summary row -->
                                    <tr class="bg-muted/20 border-b">
                                        <td class="p-3"></td>
                                        <td class="p-3">
                                            <div class="flex flex-col">
                                                <span class="text-muted-foreground text-xs">Dialokasikan</span>
                                                <span>{{ formatCurrency(group.totalAllocated) }}</span>
                                            </div>
                                        </td>
                                        <td class="p-3 text-red-500">{{ formatCurrency(group.totalSpent) }}</td>
                                        <td class="p-3">
                                            <div class="flex flex-col">
                                                <span class="text-muted-foreground text-xs">Target</span>
                                                <span>{{ formatCurrency(group.totalTarget) }}</span>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Category rows (shown when group is active) -->
                                    <template v-if="isGroupActive(group.id)">
                                        <tr v-for="category in group.categories" :key="category.id" class="hover:bg-muted/20 border-b">
                                            <td class="p-3 pl-10">
                                                <div class="flex items-center gap-2">
                                                    <span>{{ category.icon }}</span>
                                                    {{ category.name }}
                                                </div>
                                            </td>
                                            <td class="p-3">{{ formatCurrency(category.allocated) }}</td>
                                            <td class="p-3 text-red-500">{{ formatCurrency(category.spent) }}</td>
                                            <td class="p-3">{{ formatCurrency(category.target) }}</td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
