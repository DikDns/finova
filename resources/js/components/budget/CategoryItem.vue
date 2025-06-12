<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Check, Edit2, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Category {
    id: string;
    name: string;
    allocated: number;
    spent: number;
    target: number;
}

interface Props {
    category: Category;
    currencyCode: string;
    isLoading: boolean;
    isEditing: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    startEditing: [category: { id: string; name: string }];
    saveEdit: [categoryName: string];
    cancelEditing: [];
    delete: [category: { id: string; name: string }];
}>();

const editingCategoryName = ref('');

// Format currency
const formatCurrency = (amount: number, currencyCode = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const handleStartEditing = () => {
    editingCategoryName.value = props.category.name;
    emit('startEditing', { id: props.category.id, name: props.category.name });
};

const handleSaveEdit = () => {
    if (editingCategoryName.value.trim()) {
        emit('saveEdit', editingCategoryName.value.trim());
    }
};

const handleDelete = () => {
    emit('delete', { id: props.category.id, name: props.category.name });
};
</script>

<template>
    <div
        class="hover:bg-muted/50 group flex border-t px-6 py-3 text-sm"
        :class="isEditing ? 'pointer-events-none' : ''"
    >
        <div class="flex min-w-0 flex-1 items-center gap-x-4">
            <div class="flex h-4 w-4 items-center justify-center"></div>
            <!-- Category Name Editing -->
            <div
                v-if="isEditing"
                class="pointer-events-auto flex min-w-0 flex-1 items-center gap-2"
            >
                <Input
                    v-model="editingCategoryName"
                    class="h-8 text-sm"
                    @keyup.enter="handleSaveEdit"
                    @keyup.escape="emit('cancelEditing')"
                    @click.stop
                    :disabled="isLoading"
                />
                <Button
                    size="sm"
                    variant="ghost"
                    @click.stop="handleSaveEdit"
                    :disabled="!editingCategoryName.trim() || isLoading"
                >
                    <Check class="h-3 w-3" />
                </Button>
                <Button size="sm" variant="ghost" @click.stop="emit('cancelEditing')" :disabled="isLoading">
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
                        @click="handleStartEditing"
                        :disabled="isLoading"
                    >
                        <Edit2 class="h-3 w-3" />
                    </Button>
                    <Button
                        size="sm"
                        variant="ghost"
                        @click="handleDelete"
                        :disabled="isLoading"
                    >
                        <Trash2 class="h-3 w-3" />
                    </Button>
                </div>
            </div>
        </div>
        <div class="w-32 flex-shrink-0 text-right">{{ formatCurrency(category.allocated, currencyCode) }}</div>
        <div
            class="w-32 flex-shrink-0 text-right font-medium"
            :class="category.spent >= 0 ? 'text-green-500' : 'text-red-500'"
        >
            {{ formatCurrency(category.spent, currencyCode) }}
        </div>
        <div class="w-32 flex-shrink-0 text-right">{{ formatCurrency(category.target, currencyCode) }}</div>
    </div>
</template>