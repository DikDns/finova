<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import BudgetsSidebarLayout from '@/layouts/budgets/BudgetsSidebarLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Trash2, Wallet } from 'lucide-vue-next';
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

defineProps<Props>();

// State for delete confirmation dialog
const showDeleteDialog = ref(false);
const budgetToDelete = ref<Budget | null>(null);

// State for edit dialog
const showEditDialog = ref(false);
const budgetToEdit = ref<Budget | null>(null);

const form = useForm({
    name: '',
    description: '',
});

// Format the last used time
const formatLastUsed = (date: string) => {
    const lastUsed = new Date(date);
    const now = new Date();
    const diffMs = now.getTime() - lastUsed.getTime();
    const diffMins = Math.round(diffMs / 60000);

    if (diffMins < 60) {
        return `${diffMins} menit yang lalu`;
    }

    const diffHours = Math.round(diffMins / 60);
    if (diffHours < 24) {
        return `${diffHours} jam yang lalu`;
    }

    const diffDays = Math.round(diffHours / 24);
    return `${diffDays} hari yang lalu`;
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

// Function to open edit dialog
const openEditDialog = (budget: Budget) => {
    budgetToEdit.value = budget;
    form.name = budget.name;
    form.description = budget.description;
    showEditDialog.value = true;
};

// Function to update the budget
const updateBudget = () => {
    if (budgetToEdit.value) {
        form.put(route('budgets.update', budgetToEdit.value.id), {
            onSuccess: () => {
                showEditDialog.value = false;
                budgetToEdit.value = null;
                form.reset();
            },
        });
    }
};

// Function to cancel edit
const cancelEdit = () => {
    showEditDialog.value = false;
    budgetToEdit.value = null;
    form.reset();
};
</script>

<template>
    <Head title="Budget Anda" />

    <BudgetsSidebarLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="font-serif text-2xl font-semibold tracking-tight">Budget Anda</h1>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Existing Budget Plan Cards -->
                <Card
                    v-for="budget in budgets"
                    :key="budget.id"
                    class="group overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:shadow-lg"
                >
                    <div class="relative">
                        <Link :href="route('budget', budget.id)">
                            <div class="p-6">
                                <div class="flex justify-center">
                                    <Wallet class="text-foreground h-16 w-16" />
                                </div>
                            </div>
                            <CardContent>
                                <div class="mb-4 text-center sm:mb-0">
                                    <h3 class="font-serif text-lg font-medium">{{ budget.name }}</h3>
                                    <p class="text-muted-foreground text-sm">Terakhir digunakan {{ formatLastUsed(budget.updated_at) }}</p>
                                </div>
                            </CardContent>
                        </Link>
                        <!-- Delete button (desktop) -->
                        <div class="absolute top-2 right-2 hidden md:hidden group-hover:md:block">
                            <Button variant="ghost" size="icon" class="text-muted-foreground hover:text-destructive" @click="confirmDelete(budget)">
                                <Trash2 class="h-5 w-5" />
                            </Button>
                        </div>
                        <!-- Mobile actions -->
                        <div class="flex flex-col gap-2 border-t p-4 md:hidden">
                            <Button variant="outline" size="sm" @click="openEditDialog(budget)">
                                <Edit2 class="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                            <Button variant="destructive" size="sm" @click="confirmDelete(budget)">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Hapus
                            </Button>
                        </div>
                    </div>
                </Card>

                <!-- Create New Plan Card -->
                <Card
                    class="hover:border-primary/50 flex flex-col items-center justify-center border-dashed p-6 transition-all duration-300 hover:scale-[1.02] hover:shadow-lg"
                >
                    <div class="flex w-full flex-col items-center justify-center gap-2 text-center">
                        <div class="bg-primary/10 rounded-full p-3">
                            <Plus class="text-primary h-6 w-6" />
                        </div>
                        <h3 class="font-serif text-lg font-medium">Buat Budget Baru</h3>
                        <p class="text-muted-foreground text-sm">Atur budget baru</p>
                        <!-- <Link :href="route('budgets.create')"> -->
                        <Button class="mt-2 w-full">Buat Budget</Button>
                        <!-- </Link> -->
                    </div>
                </Card>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Hapus Budget</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus "{{ budgetToDelete?.name }}"? Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="cancelDelete">Batal</Button>
                    <Button variant="destructive" @click="deleteBudget">Hapus</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Budget Dialog -->
        <Dialog :open="showEditDialog" @update:open="showEditDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Budget</DialogTitle>
                    <DialogDescription> Ubah detail budget Anda. </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="updateBudget" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Nama</Label>
                        <Input id="name" v-model="form.name" type="text" placeholder="Nama budget" />
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Deskripsi</Label>
                        <Input id="description" v-model="form.description" type="text" placeholder="Deskripsi budget" />
                    </div>

                    <DialogFooter>
                        <Button variant="outline" type="button" @click="cancelEdit">Batal</Button>
                        <Button type="submit" :disabled="form.processing">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </BudgetsSidebarLayout>
</template>
