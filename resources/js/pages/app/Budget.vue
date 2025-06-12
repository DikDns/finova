<script setup lang="ts">
import BudgetHeader from '@/components/budget/BudgetHeader.vue';
import BudgetSummaryCard from '@/components/budget/BudgetSummaryCard.vue';
import CategoryGroupItem from '@/components/budget/CategoryGroupItem.vue';
import DeleteConfirmationDialogs from '@/components/budget/DeleteConfirmationDialogs.vue';
import NewGroupForm from '@/components/budget/NewGroupForm.vue';
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Budget } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Props {
    budget: Budget;
}

const props = defineProps<Props>();

// Current month state
const currentMonth = ref(new Date());

// Editing states
const editingGroupId = ref<string | null>(null);
const editingCategoryId = ref<string | null>(null);
const editingGroupName = ref('');
const editingCategoryName = ref('');
const isCreatingGroup = ref(false);
const isCreatingCategory = ref<string | null>(null);
const newGroupName = ref('');
const newCategoryName = ref('');
const isLoading = ref(false);

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

// CRUD Functions
const startEditingGroup = (groupId: string, groupName: string) => {
    editingGroupId.value = groupId;
    editingGroupName.value = groupName;
};

const startEditingCategory = (categoryId: string, categoryName: string) => {
    editingCategoryId.value = categoryId;
    editingCategoryName.value = categoryName;
};

const cancelEditing = () => {
    editingGroupId.value = null;
    editingCategoryId.value = null;
    editingGroupName.value = '';
    editingCategoryName.value = '';
    isCreatingGroup.value = false;
    isCreatingCategory.value = null;
    newGroupName.value = '';
    newCategoryName.value = '';
};

const saveGroupEdit = async () => {
    if (!editingGroupId.value || !editingGroupName.value.trim()) return;

    isLoading.value = true;
    try {
        await router.put(route('category-groups.update', editingGroupId.value), {
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
        await router.put(route('categories.update', editingCategoryId.value), {
            name: editingCategoryName.value.trim(),
        });
        cancelEditing();
    } catch (error) {
        console.error('Error updating category:', error);
    } finally {
        isLoading.value = false;
    }
};

const deleteGroup = async (groupId: string, groupName: string) => {
    if (!confirm(`Apakah Anda yakin ingin menghapus grup "${groupName}"?`)) return;

    isLoading.value = true;
    try {
        await router.delete(route('category-groups.destroy', groupId));
    } catch (error) {
        console.error('Error deleting group:', error);
    } finally {
        isLoading.value = false;
    }
};

const deleteCategory = async (categoryId: string, categoryName: string) => {
    if (!confirm(`Apakah Anda yakin ingin menghapus kategori "${categoryName}"?`)) return;

    isLoading.value = true;
    try {
        await router.delete(route('categories.destroy', categoryId));
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
    isCreatingCategory.value = groupId;
    newCategoryName.value = '';
};

const saveNewGroup = async () => {
    if (!newGroupName.value.trim()) return;

    isLoading.value = true;
    try {
        await router.post(route('category-groups.store'), {
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
        await router.post(route('categories.store'), {
            name: newCategoryName.value.trim(),
            category_group_id: isCreatingCategory.value,
        });
        cancelEditing();
    } catch (error) {
        console.error('Error creating category:', error);
    } finally {
        isLoading.value = false;
    }
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

    <AppLayout :breadcrumbs="breadcrumbs" :budget_id="props.budget.id">
        <div class="p-6">
            <div class="flex flex-col gap-6">
                <BudgetHeader
                    :budget-name="props.budget.name"
                    :current-month="currentMonth"
                    @prev-month="goToPrevMonth"
                    @next-month="goToNextMonth"
                />

                <BudgetSummaryCard :amount="budget.amount" :currency-code="budget.currency_code" />

                <NewGroupForm v-if="isCreatingGroup" :is-loading="isLoading" @save="saveNewGroup" @cancel="cancelEditing" />
                <div v-else class="flex items-center gap-2">
                    <Button variant="outline" class="flex items-center gap-2" @click="startCreatingGroup" :disabled="isLoading">
                        <Plus class="h-4 w-4" />
                        <span>Tambah Grup Kategori</span>
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
                        <CategoryGroupItem
                            v-for="group in groupedCategories"
                            :key="group?.id"
                            :group="group"
                            :budget="budget"
                            :editing-group-id="editingGroupId"
                            :editing-group-name="editingGroupName"
                            :editing-category-id="editingCategoryId"
                            :editing-category-name="editingCategoryName"
                            :is-creating-category="isCreatingCategory"
                            :new-category-name="newCategoryName"
                            :is-loading="isLoading"
                            @start-editing-group="startEditingGroup"
                            @start-editing-category="startEditingCategory"
                            @start-creating-category="startCreatingCategory"
                            @save-group-edit="saveGroupEdit"
                            @save-category-edit="saveCategoryEdit"
                            @save-new-category="saveNewCategory"
                            @delete-group="deleteGroup"
                            @delete-category="deleteCategory"
                            @cancel-editing="cancelEditing"
                        />
                    </Accordion>
                </main>
            </div>
        </div>

        <!-- Delete Confirmation Dialogs -->
        <DeleteConfirmationDialogs
            :show-group-dialog="showDeleteGroupDialog"
            :show-category-dialog="showDeleteCategoryDialog"
            :deleting-group-name="deletingGroupName"
            :deleting-category-name="deletingCategoryName"
            :is-loading="isLoading"
            @confirm-delete-group="confirmDeleteGroup"
            @confirm-delete-category="confirmDeleteCategory"
            @cancel-delete="
                showDeleteGroupDialog = false;
                showDeleteCategoryDialog = false;
            "
        />
    </AppLayout>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
