<script setup lang="ts">
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Budget } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Check, ChevronLeft, ChevronRight, Edit2, Plus, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    budget: Budget;
}

const props = defineProps<Props>();

// Current month state
const currentMonth = ref(new Date());
const showMonthlyBudgetDialog = ref(false);
const newMonthDate = ref<Date | null>(null);
const isNextMonth = ref(false);

// Editing states
const editingGroupId = ref<string | null>(null);
const editingCategoryId = ref<string | null>(null);
const editingGroupName = ref('');
const editingCategoryName = ref('');
const editingAllocatedBudgetId = ref<string | null>(null);
const editingAllocatedAmount = ref('');
const editingTargetBudgetId = ref<string | null>(null);
const editingTargetAmount = ref('');
const isCreatingGroup = ref(false);
const isCreatingCategory = ref<string | null>(null);
const newGroupName = ref('');
const newCategoryName = ref('');
const isLoading = ref(false);

// Delete confirmation dialog states
const showDeleteGroupDialog = ref(false);
const showDeleteCategoryDialog = ref(false);
const groupToDelete = ref<{ id: string; name: string } | null>(null);
const categoryToDelete = ref<{ id: string; name: string } | null>(null);

// Format currency
const formatCurrency = (amount: number, currencyCode = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Format month for display
const formattedMonth = computed(() => {
    return currentMonth.value.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
});

// Format month for API
const formatMonthForAPI = (date: Date) => {
    return date.toLocaleDateString('en-CA', { month: 'long', year: 'numeric' });
};

// Check if monthly budget exists
const hasMonthlyBudget = (date: Date) => {
    const monthStr = formatMonthForAPI(date);
    return props.budget.monthly_budgets?.some((mb) => mb.month === monthStr) ?? false;
};

// Navigate to previous month
const goToPrevMonth = () => {
    const newDate = new Date(currentMonth.value);
    newDate.setMonth(newDate.getMonth() - 1);

    if (!hasMonthlyBudget(newDate)) {
        newMonthDate.value = newDate;
        isNextMonth.value = false;
        showMonthlyBudgetDialog.value = true;
    } else {
        currentMonth.value = newDate;
    }
};

// Navigate to next month
const goToNextMonth = () => {
    const newDate = new Date(currentMonth.value);
    newDate.setMonth(newDate.getMonth() + 1);

    if (!hasMonthlyBudget(newDate)) {
        newMonthDate.value = newDate;
        isNextMonth.value = true;
        showMonthlyBudgetDialog.value = true;
    } else {
        currentMonth.value = newDate;
    }
};

// Create new monthly budget
const createMonthlyBudget = async () => {
    if (!newMonthDate.value) return;

    isLoading.value = true;
    try {
        router.post(route('monthly-budgets.store'), {
            budget_id: props.budget.id,
            month: newMonthDate.value,
            reference_month: currentMonth.value,
        });

        currentMonth.value = newMonthDate.value;
        showMonthlyBudgetDialog.value = false;
        newMonthDate.value = null;
    } catch (error) {
        console.error('Error creating monthly budget:', error);
    } finally {
        isLoading.value = false;
    }
};

// Cancel monthly budget creation
const cancelMonthlyBudget = () => {
    showMonthlyBudgetDialog.value = false;
    newMonthDate.value = null;
};

// CRUD Functions
const startEditingGroup = (groupId: string, groupName: string) => {
    editingCategoryId.value = null;
    editingCategoryName.value = '';
    editingAllocatedBudgetId.value = null;
    editingAllocatedAmount.value = '';
    editingTargetBudgetId.value = null;
    editingTargetAmount.value = '';
    isCreatingGroup.value = false;
    isCreatingCategory.value = null;
    newGroupName.value = '';
    newCategoryName.value = '';
    editingGroupId.value = groupId;
    editingGroupName.value = groupName;
};

const startEditingCategory = (categoryId: string, categoryName: string) => {
    editingGroupId.value = null;
    editingGroupName.value = '';
    editingAllocatedBudgetId.value = null;
    editingAllocatedAmount.value = '';
    editingTargetBudgetId.value = null;
    editingTargetAmount.value = '';
    isCreatingGroup.value = false;
    isCreatingCategory.value = null;
    newGroupName.value = '';
    newCategoryName.value = '';
    editingCategoryId.value = categoryId;
    editingCategoryName.value = categoryName;
};

const startEditingAllocated = (categoryBudgetId: string, allocatedAmount: string) => {
    editingGroupId.value = null;
    editingGroupName.value = '';
    editingCategoryId.value = null;
    editingCategoryName.value = '';
    editingTargetBudgetId.value = null;
    editingTargetAmount.value = '';
    isCreatingGroup.value = false;
    isCreatingCategory.value = null;
    newGroupName.value = '';
    newCategoryName.value = '';
    editingAllocatedBudgetId.value = categoryBudgetId;
    editingAllocatedAmount.value = allocatedAmount;
};

const startEditingTarget = (categoryBudgetId: string, targetAmount: string) => {
    editingGroupId.value = null;
    editingGroupName.value = '';
    editingCategoryId.value = null;
    editingCategoryName.value = '';
    editingAllocatedBudgetId.value = null;
    editingAllocatedAmount.value = '';
    isCreatingGroup.value = false;
    isCreatingCategory.value = null;
    newGroupName.value = '';
    newCategoryName.value = '';
    editingTargetBudgetId.value = categoryBudgetId;
    editingTargetAmount.value = targetAmount;
};

const saveGroupEdit = async () => {
    if (!editingGroupId.value || !editingGroupName.value.trim()) return;

    isLoading.value = true;
    try {
        router.put(route('category-groups.update', editingGroupId.value), {
            name: editingGroupName.value.trim(),
        });
        cancelEditing();
    } catch (error) {
        console.error('Error updating group:', error);
    } finally {
        isLoading.value = false;
    }
};

const saveCategoryEdit = async () => {
    if (!editingCategoryId.value || !editingCategoryName.value.trim()) return;

    isLoading.value = true;
    try {
        router.put(route('categories.update', editingCategoryId.value), {
            name: editingCategoryName.value.trim(),
        });
        cancelEditing();
    } catch (error) {
        console.error('Error updating category:', error);
    } finally {
        isLoading.value = false;
    }
};

const saveAllocatedEdit = async () => {
    if (!editingAllocatedBudgetId.value) return;

    const amount = parseFloat(editingAllocatedAmount.value);
    if (isNaN(amount) || amount < 0) return;

    isLoading.value = true;
    try {
        router.put(route('category-budgets.update', editingAllocatedBudgetId.value), {
            assigned: amount,
        });
        cancelEditing();
    } catch (error) {
        console.error('Error updating allocated amount:', error);
    } finally {
        isLoading.value = false;
    }
};

const saveTargetEdit = async () => {
    if (!editingTargetBudgetId.value) return;

    const amount = parseFloat(editingTargetAmount.value);
    if (isNaN(amount) || amount < 0) return;

    isLoading.value = true;
    try {
        router.put(route('category-budgets.update', editingTargetBudgetId.value), {
            available: amount,
        });
        cancelEditing();
    } catch (error) {
        console.error('Error updating target amount:', error);
    } finally {
        isLoading.value = false;
    }
};

const deleteGroup = (groupId: string, groupName: string) => {
    groupToDelete.value = { id: groupId, name: groupName };
    showDeleteGroupDialog.value = true;
};

const confirmDeleteGroup = async () => {
    if (!groupToDelete.value) return;

    isLoading.value = true;
    try {
        router.delete(route('category-groups.destroy', groupToDelete.value.id));
        showDeleteGroupDialog.value = false;
        groupToDelete.value = null;
    } catch (error) {
        console.error('Error deleting group:', error);
    } finally {
        isLoading.value = false;
    }
};

const deleteCategory = (categoryId: string, categoryName: string) => {
    categoryToDelete.value = { id: categoryId, name: categoryName };
    showDeleteCategoryDialog.value = true;
};

const confirmDeleteCategory = async () => {
    if (!categoryToDelete.value) return;

    isLoading.value = true;
    try {
        router.delete(route('categories.destroy', categoryToDelete.value.id));
        showDeleteCategoryDialog.value = false;
        categoryToDelete.value = null;
    } catch (error) {
        console.error('Error deleting category:', error);
    } finally {
        isLoading.value = false;
    }
};

const startCreatingGroup = () => {
    isCreatingGroup.value = true;
    newGroupName.value = '';
};

const startCreatingCategory = (groupId: string) => {
    editingGroupId.value = null;
    editingGroupName.value = '';
    editingCategoryId.value = null;
    editingCategoryName.value = '';
    editingAllocatedBudgetId.value = null;
    editingAllocatedAmount.value = '';
    editingTargetBudgetId.value = null;
    editingTargetAmount.value = '';
    isCreatingGroup.value = false;
    newGroupName.value = '';
    isCreatingCategory.value = groupId;
    newCategoryName.value = '';
};

const saveNewGroup = async () => {
    if (!newGroupName.value.trim()) return;

    isLoading.value = true;
    try {
        router.post(route('category-groups.store'), {
            name: newGroupName.value.trim(),
            budget_id: props.budget.id,
        });
        cancelEditing();
    } catch (error) {
        console.error('Error creating group:', error);
    } finally {
        isLoading.value = false;
    }
};

const saveNewCategory = async () => {
    if (!newCategoryName.value.trim() || !isCreatingCategory.value) return;

    isLoading.value = true;
    try {
        router.post(route('categories.store'), {
            name: newCategoryName.value.trim(),
            category_group_id: isCreatingCategory.value,
            monthly_budget_ids: props.budget.monthly_budgets.map((mb) => mb.id),
        });
        cancelEditing();
    } catch (error) {
        console.error('Error creating category:', error);
    } finally {
        isLoading.value = false;
    }
};

const cancelEditing = () => {
    editingGroupId.value = null;
    editingGroupName.value = '';
    editingCategoryId.value = null;
    editingCategoryName.value = '';
    editingAllocatedBudgetId.value = null;
    editingAllocatedAmount.value = '';
    editingTargetBudgetId.value = null;
    editingTargetAmount.value = '';
    isCreatingGroup.value = false;
    isCreatingCategory.value = null;
    newGroupName.value = '';
    newCategoryName.value = '';
};

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

                    return {
                        id: category.id,
                        name: category.name,
                        allocated: categoryBudget ? parseFloat(categoryBudget.assigned) : 0,
                        spent: categoryBudget ? parseFloat(categoryBudget.activity) : 0,
                        target: categoryBudget ? parseFloat(categoryBudget.available) : 0,
                        category_budget: categoryBudget,
                    };
                })
                .filter(Boolean);

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

    <AppLayout :budget_id="props.budget.id">
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
                                <span class="text-xl font-semibold">{{
                                    formatCurrency(parseFloat(currentMonthBudget?.total_balance ?? '0'), budget.currency_code)
                                }}</span>
                            </div>
                            <p class="text-muted-foreground text-sm">Siap dialokasikan</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" class="flex items-center gap-2" @click="startCreatingGroup" :disabled="isLoading">
                        <Plus class="h-4 w-4" />
                        <span>Tambah Grup Kategori</span>
                    </Button>
                </div>

                <main class="overflow-hidden rounded-lg border">
                    <div class="bg-muted/50 flex px-6 py-3 text-sm font-medium">
                        <div class="min-w-0 flex-1">KATEGORI</div>
                        <div class="w-32 flex-shrink-0 text-right">DIALOKASIKAN</div>
                        <div class="w-32 flex-shrink-0 text-right">TARGET</div>
                        <div class="w-32 flex-shrink-0 text-right">AKTIVITAS</div>
                    </div>

                    <Accordion type="multiple" class="space-y-0">
                        <!-- New Group Creation Row -->
                        <div v-if="isCreatingGroup" class="bg-muted/30 border-b px-6 py-3">
                            <div class="flex w-full">
                                <div class="flex min-w-0 flex-1 items-center gap-2">
                                    <Input
                                        v-model="newGroupName"
                                        placeholder="Nama grup kategori baru"
                                        class="h-8 text-sm"
                                        @keyup.enter="saveNewGroup"
                                        @keyup.escape="cancelEditing"
                                        :disabled="isLoading"
                                    />
                                    <Button size="sm" variant="ghost" @click="saveNewGroup" :disabled="!newGroupName.trim() || isLoading">
                                        <Check class="h-3 w-3" />
                                    </Button>
                                    <Button size="sm" variant="ghost" @click.stop="cancelEditing" :disabled="isLoading">
                                        <X class="h-3 w-3" />
                                    </Button>
                                </div>
                                <div class="text-muted-foreground w-32 flex-shrink-0 text-right font-medium">-</div>
                                <div class="text-muted-foreground w-32 flex-shrink-0 text-right font-medium">-</div>
                                <div class="text-muted-foreground w-32 flex-shrink-0 text-right font-medium">-</div>
                            </div>
                        </div>

                        <AccordionItem v-for="group in groupedCategories" :key="group?.id" :value="group?.id ?? ''" class="border-0 border-b">
                            <AccordionTrigger
                                class="bg-muted/20 hover:bg-muted/50 group px-6 py-3 hover:no-underline"
                                :disabled="editingGroupId === group?.id"
                            >
                                <div class="flex w-full" :class="editingGroupId === group?.id ? 'pointer-events-none' : ''">
                                    <div class="flex min-w-0 flex-1 items-center gap-2">
                                        <!-- Group Name Editing -->
                                        <div v-if="editingGroupId === group?.id" class="pointer-events-auto flex flex-1 items-center gap-2">
                                            <Input
                                                v-model="editingGroupName"
                                                class="h-8 text-sm font-medium"
                                                @keyup.enter="saveGroupEdit"
                                                @keyup.escape="cancelEditing"
                                                @click.stop
                                                :disabled="isLoading"
                                            />
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                @click.stop="saveGroupEdit"
                                                :disabled="!editingGroupName.trim() || isLoading"
                                            >
                                                <Check class="h-3 w-3" />
                                            </Button>
                                            <Button size="sm" variant="ghost" @click.stop="cancelEditing" :disabled="isLoading">
                                                <X class="h-3 w-3" />
                                            </Button>
                                        </div>
                                        <!-- Group Name Display -->
                                        <div v-else class="flex flex-1 items-center gap-2">
                                            <span class="font-semibold">{{ group?.name }}</span>
                                            <div class="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    @click.stop="startEditingGroup(group?.id ?? '', group?.name ?? '')"
                                                    :disabled="isLoading"
                                                >
                                                    <Edit2 class="h-3 w-3" />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    @click.stop="deleteGroup(group?.id ?? '', group?.name ?? '')"
                                                    :disabled="isLoading"
                                                >
                                                    <Trash2 class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-32 flex-shrink-0 text-right font-semibold">
                                        {{ formatCurrency(group?.totalAllocated ?? 0, budget.currency_code) }}
                                    </div>
                                    <div class="w-32 flex-shrink-0 text-right font-semibold">
                                        {{ formatCurrency(group?.totalTarget ?? 0, budget.currency_code) }}
                                    </div>
                                    <div
                                        class="w-32 flex-shrink-0 text-right font-semibold"
                                        :class="(group?.totalSpent ?? 0) >= 0 ? 'text-green-500' : 'text-red-500'"
                                    >
                                        {{ formatCurrency(group?.totalSpent ?? 0, budget.currency_code) }}
                                    </div>
                                </div>
                            </AccordionTrigger>

                            <AccordionContent class="pt-0 pb-0">
                                <!-- Add Category Button -->
                                <div class="bg-muted/20 border-t px-6 py-2">
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        class="flex items-center gap-2 text-xs"
                                        @click="startCreatingCategory(group?.id ?? '')"
                                        :disabled="isLoading"
                                    >
                                        <Plus class="h-3 w-3" />
                                        <span>Tambah Kategori</span>
                                    </Button>
                                </div>

                                <!-- New Category Creation Row -->
                                <div v-if="isCreatingCategory === group?.id" class="bg-muted/30 border-t px-6 py-3">
                                    <div class="flex text-sm">
                                        <div class="flex min-w-0 flex-1 items-center gap-x-4">
                                            <div class="flex h-4 w-4 items-center justify-center"></div>
                                            <div class="flex min-w-0 flex-1 items-center gap-2">
                                                <Input
                                                    v-model="newCategoryName"
                                                    placeholder="Nama kategori baru"
                                                    class="h-8 text-sm"
                                                    @keyup.enter="saveNewCategory"
                                                    @keyup.escape="cancelEditing"
                                                    @click.stop
                                                    :disabled="isLoading"
                                                />
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    @click.stop="saveNewCategory"
                                                    :disabled="!newCategoryName.trim() || isLoading"
                                                >
                                                    <Check class="h-3 w-3" />
                                                </Button>
                                                <Button size="sm" variant="ghost" @click.stop="cancelEditing" :disabled="isLoading">
                                                    <X class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                        <div class="text-muted-foreground w-32 flex-shrink-0 text-right">-</div>
                                        <div class="text-muted-foreground w-32 flex-shrink-0 text-right">-</div>
                                        <div class="text-muted-foreground w-32 flex-shrink-0 text-right">-</div>
                                    </div>
                                </div>

                                <!-- Categories -->
                                <div
                                    v-for="category in group?.categories"
                                    :key="category.id"
                                    class="hover:bg-muted/50 group flex border-t px-6 py-3 text-sm"
                                    :class="editingCategoryId === category.id ? 'pointer-events-none' : ''"
                                >
                                    <div class="flex min-w-0 flex-1 items-center gap-x-4">
                                        <div class="flex h-4 w-4 items-center justify-center"></div>
                                        <!-- Category Name Editing -->
                                        <div
                                            v-if="editingCategoryId === category.id"
                                            class="pointer-events-auto flex min-w-0 flex-1 items-center gap-2"
                                        >
                                            <Input
                                                v-model="editingCategoryName"
                                                class="h-8 text-sm"
                                                @keyup.enter="saveCategoryEdit"
                                                @keyup.escape="cancelEditing"
                                                @click.stop
                                                :disabled="isLoading"
                                            />
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                @click.stop="saveCategoryEdit"
                                                :disabled="!editingCategoryName.trim() || isLoading"
                                            >
                                                <Check class="h-3 w-3" />
                                            </Button>
                                            <Button size="sm" variant="ghost" @click.stop="cancelEditing" :disabled="isLoading">
                                                <X class="h-3 w-3" />
                                            </Button>
                                        </div>
                                        <!-- Category Name Display -->
                                        <div v-else class="flex min-w-0 flex-1 items-center gap-2">
                                            <span class="truncate" :title="category.name">{{ category.name }}</span>
                                            <div class="flex flex-shrink-0 items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    @click="startEditingCategory(category.id, category.name)"
                                                    :disabled="isLoading"
                                                >
                                                    <Edit2 class="h-3 w-3" />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    @click="deleteCategory(category.id, category.name)"
                                                    :disabled="isLoading"
                                                >
                                                    <Trash2 class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex w-32 flex-shrink-0 items-center justify-end">
                                        <div
                                            v-if="editingAllocatedBudgetId === category.category_budget?.id"
                                            class="relative flex items-center space-x-1"
                                        >
                                            <div class="absolute -right-32 z-10 flex items-center gap-1">
                                                <Input
                                                    v-model="editingAllocatedAmount"
                                                    class="h-8 min-w-32"
                                                    type="number"
                                                    :disabled="isLoading"
                                                    @keyup.enter="saveAllocatedEdit"
                                                    @keyup.escape="cancelEditing"
                                                />
                                                <Button size="sm" variant="ghost" @click.stop="saveAllocatedEdit">
                                                    <Check class="h-3 w-3" />
                                                </Button>
                                                <Button size="sm" variant="ghost" @click.stop="cancelEditing">
                                                    <X class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="flex w-fit cursor-pointer items-center justify-end space-x-1 hover:underline"
                                            @click="startEditingAllocated(category.category_budget?.id ?? '', category.allocated.toString())"
                                        >
                                            <span>{{ formatCurrency(category.allocated, budget.currency_code) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex w-32 flex-shrink-0 items-center justify-end">
                                        <div
                                            v-if="editingTargetBudgetId === category.category_budget?.id"
                                            class="relative flex items-center space-x-1"
                                        >
                                            <div class="absolute -right-32 z-10 flex items-center gap-1">
                                                <Input
                                                    v-model="editingTargetAmount"
                                                    class="h-8 min-w-32"
                                                    type="number"
                                                    :disabled="isLoading"
                                                    @keyup.enter="saveTargetEdit"
                                                    @keyup.escape="cancelEditing"
                                                />
                                                <Button size="sm" variant="ghost" @click.stop="saveTargetEdit">
                                                    <Check class="h-3 w-3" />
                                                </Button>
                                                <Button size="sm" variant="ghost" @click.stop="cancelEditing">
                                                    <X class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="flex w-fit cursor-pointer items-center justify-end space-x-1 hover:underline"
                                            @click="startEditingTarget(category.category_budget?.id ?? '', category.target.toString())"
                                        >
                                            <span :class="editingAllocatedBudgetId !== category.category_budget?.id ? '' : 'opacity-0'">{{
                                                formatCurrency(category.target, budget.currency_code)
                                            }}</span>
                                        </div>
                                    </div>
                                    <div
                                        class="flex w-32 flex-shrink-0 items-center justify-end font-medium"
                                        :class="[
                                            category.spent >= 0 ? 'text-green-500' : 'text-red-500',
                                            category.category_budget?.id !== editingTargetBudgetId ? '' : 'opacity-0',
                                        ]"
                                    >
                                        <span> {{ formatCurrency(category.spent, budget.currency_code) }}</span>
                                    </div>
                                </div>
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </main>
            </div>
        </div>

        <!-- Delete Group Confirmation Dialog -->
        <AlertDialog v-model:open="showDeleteGroupDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Konfirmasi Hapus Grup</AlertDialogTitle>
                    <AlertDialogDescription>
                        Apakah Anda yakin ingin menghapus grup "{{ groupToDelete?.name }}"? Tindakan ini tidak dapat dibatalkan dan akan menghapus
                        semua kategori dalam grup ini.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="showDeleteGroupDialog = false">Batal</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDeleteGroup" :disabled="isLoading" variant="destructive">
                        {{ isLoading ? 'Menghapus...' : 'Hapus' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Delete Category Confirmation Dialog -->
        <AlertDialog v-model:open="showDeleteCategoryDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Konfirmasi Hapus Kategori</AlertDialogTitle>
                    <AlertDialogDescription>
                        Apakah Anda yakin ingin menghapus kategori "{{ categoryToDelete?.name }}"? Tindakan ini tidak dapat dibatalkan.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="showDeleteCategoryDialog = false">Batal</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDeleteCategory" :disabled="isLoading" variant="destructive">
                        {{ isLoading ? 'Menghapus...' : 'Hapus' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Monthly Budget Creation Dialog -->
        <AlertDialog :open="showMonthlyBudgetDialog" @update:open="showMonthlyBudgetDialog = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Buat Budget Bulanan Baru</AlertDialogTitle>
                    <AlertDialogDescription>
                        Budget untuk bulan {{ newMonthDate?.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) }} belum tersedia. Apakah
                        Anda ingin membuat budget baru dengan menggunakan alokasi dari bulan
                        {{ currentMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) }}?
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="cancelMonthlyBudget">Batal</AlertDialogCancel>
                    <AlertDialogAction @click="createMonthlyBudget">Buat Budget</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
