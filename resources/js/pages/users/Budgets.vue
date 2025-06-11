<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Wallet, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Budget {
    id: string;
    name: string;
    description: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    budgets: Budget[];
}

const props = defineProps<Props>();

// State for delete confirmation dialog
const showDeleteDialog = ref(false);
const budgetToDelete = ref<Budget | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Budget Plans',
        href: route('budgets.index'),
    },
];

// Format the last used time
const formatLastUsed = (date: string) => {
    const lastUsed = new Date(date);
    const now = new Date();
    const diffMs = now.getTime() - lastUsed.getTime();
    const diffMins = Math.round(diffMs / 60000);

    if (diffMins < 60) {
        return `Last used ${diffMins} minute${diffMins !== 1 ? 's' : ''} ago`;
    }

    const diffHours = Math.round(diffMins / 60);
    if (diffHours < 24) {
        return `Last used ${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
    }

    const diffDays = Math.round(diffHours / 24);
    return `Last used ${diffDays} day${diffDays !== 1 ? 's' : ''} ago`;
};

// Function to open delete confirmation dialog
const confirmDelete = (budget: Budget) => {
    budgetToDelete.value = budget;
    showDeleteDialog.value = true;
};

// Function to delete the budget
const deleteBudget = () => {
    if (budgetToDelete.value) {
        router.delete(route('budgets.destroy', budgetToDelete.value.id), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                budgetToDelete.value = null;
            },
        });
    }
};

// Function to cancel deletion
const cancelDelete = () => {
    showDeleteDialog.value = false;
    budgetToDelete.value = null;
};
</script>

<template>
    <Head title="Budget Plans" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6">
                <h1 class="font-serif text-2xl font-semibold tracking-tight">Your Plans</h1>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Existing Budget Plan Cards -->
                <Card v-for="budget in budgets" :key="budget.id" class="overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="relative">
                        <Link :href="route('budgets.show', budget.id)">
                            <div class="p-6">
                                <div class="flex justify-center">
                                    <Wallet class="text-foreground h-16 w-16" />
                                </div>
                            </div>
                            <CardContent>
                                <div class="text-center">
                                    <h3 class="font-serif text-lg font-medium">{{ budget.name }}</h3>
                                    <p class="text-muted-foreground text-sm">{{ formatLastUsed(budget.updated_at) }}</p>
                                </div>
                            </CardContent>
                        </Link>
                        <!-- Delete button -->
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            class="absolute top-2 right-2 text-muted-foreground hover:text-destructive"
                            @click="confirmDelete(budget)"
                        >
                            <Trash2 class="h-5 w-5" />
                        </Button>
                    </div>
                </Card>

                <!-- Create New Plan Card -->
                <Card
                    class="hover:border-primary/50 flex flex-col items-center justify-center border-dashed p-6 transition-all duration-300 hover:shadow-md"
                >
                    <div class="flex flex-col items-center justify-center gap-2 text-center">
                        <div class="bg-primary/10 rounded-full p-3">
                            <Plus class="text-primary h-6 w-6" />
                        </div>
                        <h3 class="font-serif text-lg font-medium">Create New Plan</h3>
                        <p class="text-muted-foreground text-sm">Set up a new budget plan</p>
                        <Link :href="route('budgets.create')">
                            <Button class="mt-2">Create Plan</Button>
                        </Link>
                    </div>
                </Card>

                <!-- Show placeholder card if no budgets exist -->
                <Card v-if="budgets.length === 0" class="overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex justify-center">
                            <img src="/images/budget-plan-icon.svg" alt="Budget Plan" class="text-primary h-16 w-16 opacity-50" />
                        </div>
                    </div>
                    <CardContent>
                        <div class="text-center">
                            <h3 class="text-muted-foreground font-serif text-lg font-medium">Sample Budget</h3>
                            <p class="text-muted-foreground text-sm">Create your first budget plan</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>

    <!-- Delete Confirmation Dialog -->
    <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete Budget Plan</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete "{{ budgetToDelete?.name }}"? This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="cancelDelete">Cancel</Button>
                <Button variant="destructive" @click="deleteBudget">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
