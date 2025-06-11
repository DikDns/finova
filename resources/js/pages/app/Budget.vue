<script setup lang="ts">
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Category {
    id: string;
    name: string;
    budget_id: string;
    category_group_id: string;
    category_budgets: CategoryBudget[];
}

interface CategoryBudget {
    id: string;
    assigned: string;
    activity: string;
    available: string;
    category_id: string;
    created_at: string;
    updated_at: string;
    monthly_budget: MonthlyBudget;
}

interface CategoryGroup {
    id: string;
    name: string;
    budget_id: string;
    categories: Category[];
}

interface MonthlyBudget {
    id: string;
    month: string;
    total_income: string;
    total_assigned: string;
    total_activity: string;
    total_available: string;
    created_at: string;
    updated_at: string;
}

interface Budget {
    id: string;
    name: string;
    description: string;
    amount: string;
    currency_code: string;
    monthly_budgets: MonthlyBudget[];
    category_groups: CategoryGroup[];
    created_at: string;
    updated_at: string;
}

interface Props {
    budget: Budget;
}

const props = defineProps<Props>();

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

// Breadcrumbs for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.budget.name,
        href: route('budget', props.budget.id),
    },
];

console.log(props.budget);

// Get current month's budget data
const currentMonthBudget = computed(() => {
    const currentMonthStr = currentMonth.value.toLocaleDateString('en-CA', { month: 'long', year: 'numeric' }); // Format: YYYY-MM
    console.log('Current month string:', currentMonthStr);
    const monthBudget = props.budget.monthly_budgets?.find((mb) => {
        console.log('Comparing:', mb.month, 'with', currentMonthStr);
        return mb.month === currentMonthStr;
    });
    console.log('Found monthly budget:', monthBudget);
    return monthBudget;
});

// Group categories with their budgets
const groupedCategories = computed(() => {
    if (!props.budget?.category_groups) return [];

    return props.budget.category_groups
        .map((group) => {
            if (!group?.categories) return null;

            const categories = group?.categories
                .map((category) => {
                    // Find the category budget for the current month by matching the monthly budget ID
                    const categoryBudget = category.category_budgets?.find((budget) => {
                        return budget.monthly_budget?.id === currentMonthBudget.value?.id;
                    });

                    console.log('Category:', category.name, 'Budget:', categoryBudget);

                    if (categoryBudget) {
                        console.log('Values:', {
                            assigned: parseFloat(categoryBudget.assigned),
                            activity: parseFloat(categoryBudget.activity),
                            available: parseFloat(categoryBudget.available),
                        });
                    }

                    return {
                        id: category.id,
                        name: category.name,
                        allocated: categoryBudget ? parseFloat(categoryBudget.assigned) : 0,
                        spent: categoryBudget ? parseFloat(categoryBudget.activity) : 0,
                        target: categoryBudget ? parseFloat(categoryBudget.available) : 0,
                    };
                })
                .filter(Boolean);

            if (!categories.length) return null;

            return {
                id: group.id,
                name: group.name,
                categories,
                totalAllocated: categories.reduce((sum, cat) => sum + cat.allocated, 0),
                totalSpent: categories.reduce((sum, cat) => sum + cat.spent, 0),
                totalTarget: categories.reduce((sum, cat) => sum + cat.target, 0),
            };
        })
        .filter(Boolean);
});
</script>

<template>
    <Head :title="`${props.budget.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs" :budget_id="props.budget.id">
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

                <div class="bg-card rounded-lg border p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                <span class="text-xl font-semibold">{{ formatCurrency(parseFloat(budget.amount), budget.currency_code) }}</span>
                            </div>
                            <p class="text-muted-foreground text-sm">Siap dialokasikan</p>
                        </div>
                    </div>
                </div>

                <div>
                    <Button variant="outline" class="flex items-center gap-2">
                        <Plus class="h-4 w-4" />
                        <span>Tambah Kategori</span>
                    </Button>
                </div>

                <main class="overflow-hidden rounded-lg border">
                    <div class="bg-muted/50 grid grid-cols-4 gap-y-4 px-6 py-3 text-sm font-medium">
                        <div>KATEGORI</div>
                        <div class="text-right">DIALOKASIKAN</div>
                        <div class="text-right">AKTIVITAS</div>
                        <div class="text-right">TARGET</div>
                    </div>

                    <Accordion type="multiple" class="space-y-0">
                        <AccordionItem v-for="group in groupedCategories" :key="group?.id" :value="group?.id ?? ''" class="border-0 border-b">
                            <AccordionTrigger class="hover:bg-muted/50 px-6 py-3 hover:no-underline">
                                <div class="grid w-full grid-cols-4 gap-4">
                                    <div class="font-medium">{{ group?.name }}</div>
                                    <div class="text-right font-medium">{{ formatCurrency(group?.totalAllocated ?? 0, budget.currency_code) }}</div>
                                    <div :class="['text-right font-medium', (group?.totalSpent ?? 0) >= 0 ? 'text-green-500' : 'text-red-500']">
                                        {{ formatCurrency(group?.totalSpent ?? 0, budget.currency_code) }}
                                    </div>
                                    <div class="text-right font-medium">{{ formatCurrency(group?.totalTarget ?? 0, budget.currency_code) }}</div>
                                </div>
                            </AccordionTrigger>
                            <AccordionContent class="pt-0 pb-0">
                                <div
                                    v-for="category in group?.categories"
                                    :key="category.id"
                                    class="hover:bg-muted/50 grid grid-cols-4 gap-x-0 gap-y-4 border-t px-6 py-3 text-sm"
                                >
                                    <div class="flex items-center gap-x-4">
                                        <div class="flex h-4 w-4 items-center justify-center"></div>
                                        <span>{{ category.name }}</span>
                                    </div>
                                    <div class="text-right">{{ formatCurrency(category.allocated, budget.currency_code) }}</div>
                                    <div :class="['text-right font-medium', category.spent >= 0 ? 'text-green-500' : 'text-red-500']">
                                        {{ formatCurrency(category.spent, budget.currency_code) }}
                                    </div>
                                    <div class="text-right">{{ formatCurrency(category.target, budget.currency_code) }}</div>
                                </div>
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </main>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
