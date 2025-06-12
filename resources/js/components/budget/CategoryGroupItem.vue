<script setup lang="ts">
import CategoryItem from '@/components/budget/CategoryItem.vue';
import NewCategoryForm from '@/components/budget/NewCategoryForm.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Check, Edit2, Plus, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Category {
    id: string;
    name: string;
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

interface Props {
    group: CategoryGroup;
    currencyCode: string;
    isLoading: boolean;
    editingGroupId: string | null;
    editingCategoryId: string | null;
    isCreatingCategory: string | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    startEditingGroup: (groupId: string, groupName: string) => void;
    saveGroupEdit: (groupName: string) => void;
    cancelEditing: () => void;
    deleteGroup: (groupId: string, groupName: string) => void;
    startCreatingCategory: (groupId: string) => void;
    startEditingCategory: (categoryId: string, categoryName: string) => void;
    saveCategoryEdit: (categoryName: string) => void;
    deleteCategory: (categoryId: string, categoryName: string) => void;
    saveNewCategory: (categoryName: string) => void;
}>();

const editingGroupName = ref('');

// Format currency
const formatCurrency = (amount: number, currencyCode = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const handleStartEditingGroup = () => {
    editingGroupName.value = props.group.name;
    emit('startEditingGroup', props.group.id, props.group.name);
};

const handleSaveGroupEdit = () => {
    if (editingGroupName.value.trim()) {
        emit('saveGroupEdit', editingGroupName.value.trim());
    }
};

const handleDeleteGroup = () => {
    emit('deleteGroup', props.group.id, props.group.name);
};

const handleStartCreatingCategory = () => {
    emit('startCreatingCategory', props.group.id);
};
</script>

<template>
    <AccordionItem :value="group.id" class="border-0 border-b">
        <AccordionTrigger class="hover:bg-muted/50 group px-6 py-3 hover:no-underline" :disabled="editingGroupId === group.id">
            <div class="flex w-full" :class="editingGroupId === group.id ? 'pointer-events-none' : ''">
                <div class="flex min-w-0 flex-1 items-center gap-2">
                    <!-- Group Name Editing -->
                    <div v-if="editingGroupId === group.id" class="pointer-events-auto flex flex-1 items-center gap-2">
                        <Input
                            v-model="editingGroupName"
                            class="h-8 text-sm font-medium"
                            @keyup.enter="handleSaveGroupEdit"
                            @keyup.escape="emit('cancelEditing')"
                            @click.stop
                            :disabled="isLoading"
                        />
                        <Button size="sm" variant="ghost" @click.stop="handleSaveGroupEdit" :disabled="!editingGroupName.trim() || isLoading">
                            <Check class="h-3 w-3" />
                        </Button>
                        <Button size="sm" variant="ghost" @click.stop="emit('cancelEditing')" :disabled="isLoading">
                            <X class="h-3 w-3" />
                        </Button>
                    </div>
                    <!-- Group Name Display -->
                    <div v-else class="flex flex-1 items-center gap-2">
                        <span class="font-medium">{{ group.name }}</span>
                        <div class="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                            <Button size="sm" variant="ghost" @click.stop="handleStartEditingGroup" :disabled="isLoading">
                                <Edit2 class="h-3 w-3" />
                            </Button>
                            <Button size="sm" variant="ghost" @click.stop="handleDeleteGroup" :disabled="isLoading">
                                <Trash2 class="h-3 w-3" />
                            </Button>
                        </div>
                    </div>
                </div>
                <div class="w-32 flex-shrink-0 text-right font-medium">
                    {{ formatCurrency(group.totalAllocated, currencyCode) }}
                </div>
                <div class="w-32 flex-shrink-0 text-right font-medium" :class="group.totalSpent >= 0 ? 'text-green-500' : 'text-red-500'">
                    {{ formatCurrency(group.totalSpent, currencyCode) }}
                </div>
                <div class="w-32 flex-shrink-0 text-right font-medium">
                    {{ formatCurrency(group.totalTarget, currencyCode) }}
                </div>
            </div>
        </AccordionTrigger>
        <AccordionContent class="pt-0 pb-0">
            <!-- Add Category Button -->
            <div class="bg-muted/20 border-t px-6 py-2">
                <Button size="sm" variant="ghost" class="flex items-center gap-2 text-xs" @click="handleStartCreatingCategory" :disabled="isLoading">
                    <Plus class="h-3 w-3" />
                    <span>Tambah Kategori</span>
                </Button>
            </div>

            <!-- New Category Creation Row -->
            <NewCategoryForm
                v-if="isCreatingCategory === group.id"
                :is-loading="isLoading"
                @save="emit('saveNewCategory', $event)"
                @cancel="emit('cancelEditing')"
            />

            <!-- Categories -->
            <CategoryItem
                v-for="category in group.categories"
                :key="category.id"
                :category="category"
                :currency-code="currencyCode"
                :is-loading="isLoading"
                :is-editing="editingCategoryId === category.id"
                @start-editing="emit('startEditingCategory', $event.id, $event.name)"
                @save-edit="emit('saveCategoryEdit', $event)"
                @cancel-editing="emit('cancelEditing')"
                @delete="emit('deleteCategory', $event.id, $event.name)"
            />
        </AccordionContent>
    </AccordionItem>
</template>
