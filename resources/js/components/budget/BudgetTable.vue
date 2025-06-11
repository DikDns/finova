<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChevronRight, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

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

interface Props {
    categoryGroups: CategoryGroup[];
    currencyCode?: string;
}

const props = defineProps<Props>();

// Active group state (for accordion)
const activeGroups = ref<Set<string>>(new Set());

// Format currency
const formatCurrency = (amount: number, currencyCode = props.currencyCode || 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
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

// Emit events for adding categories
const emit = defineEmits(['add-category']);

const addCategory = () => {
    emit('add-category');
};

console.log(props.categoryGroups);
</script>

<template>
    <div class="rounded-lg border shadow-sm">
        <div class="p-4">
            <Button variant="outline" class="flex items-center gap-2" @click="addCategory">
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
                    <template v-for="group in props.categoryGroups" :key="group.id">
                        <!-- Group header row -->
                        <tr class="hover:bg-muted/50 cursor-pointer border-b transition-colors" @click="toggleGroup(group.id)">
                            <td class="p-3" colspan="4">
                                <div class="flex items-center gap-2">
                                    <ChevronRight class="h-4 w-4 transition-transform" :class="{ 'rotate-90': isGroupActive(group.id) }" />
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
                            <td class="p-3" :class="{ 'text-red-500': group.totalSpent < 0 }">{{ formatCurrency(group.totalSpent) }}</td>
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
                                <td class="p-3" :class="{ 'text-red-500': category.spent < 0 }">{{ formatCurrency(category.spent) }}</td>
                                <td class="p-3">{{ formatCurrency(category.target) }}</td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
