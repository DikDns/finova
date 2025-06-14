<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Account, AccountType, Budget, Category, Transaction } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    budget: Budget;
    transactions: {
        data: Transaction[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    account_types: AccountType[];
    accounts: Account[];
    categories: Category[];
}

const props = defineProps<Props>();

// Form state
const showCreateDialog = ref(false);
const form = ref({
    payee: '',
    amount: 0,
    date: new Date().toISOString().split('T')[0],
    category_id: null as string | null,
    account_id: '' as string,
    memo: '',
    budget_id: props.budget.id,
});

// Form errors
const errors = ref<Record<string, string>>({});

// Reset form
const resetForm = () => {
    form.value = {
        payee: '',
        amount: 0,
        date: new Date().toISOString().split('T')[0],
        category_id: null,
        account_id: '',
        memo: '',
        budget_id: props.budget.id,
    };
    errors.value = {};
};

// Submit form
const submitForm = () => {
    router.post(route('transactions.store'), form.value, {
        onSuccess: () => {
            showCreateDialog.value = false;
            resetForm();
        },
        onError: (err) => {
            errors.value = err;
        },
    });
};

// Pagination
const goToPage = (page: number) => {
    router.get(
        route('accounts.index', { budget: props.budget.id }),
        {
            page: page,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Format date
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Format amount
const formatAmount = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: props.budget.currency_code,
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="Semua Rekening" />

    <AppLayout :budget_id="props.budget.id" :currency_code="props.budget.currency_code" :account_types="props.account_types">
        <div class="p-6">
            <!-- Header  -->
            <div class="mb-6 flex items-center justify-between">
                <h1 class="font-serif text-2xl font-semibold tracking-tight">Halaman Semua Rekening</h1>
            </div>

            <!-- Transactions Section -->
            <div class="overflow-hidden rounded-lg shadow">
                <div class="flex items-center justify-between border-b p-4">
                    <h2 class="font-serif text-lg">Transaksi Terbaru</h2>

                    <!-- Create Transaction Button -->
                    <Dialog v-model:open="showCreateDialog">
                        <DialogTrigger as-child>
                            <Button class="flex items-center gap-2">
                                <Plus class="h-4 w-4" />
                                <span>Tambah Transaksi</span>
                            </Button>
                        </DialogTrigger>

                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Tambah Transaksi Baru</DialogTitle>
                                <DialogDescription> Isi form berikut untuk menambahkan transaksi baru. </DialogDescription>
                            </DialogHeader>

                            <form @submit.prevent="submitForm" class="mt-4 space-y-4">
                                <!-- Payee -->
                                <div class="space-y-2">
                                    <label for="payee" class="text-sm font-medium">Penerima/Pembayar</label>
                                    <Input
                                        id="payee"
                                        v-model="form.payee"
                                        placeholder="Nama penerima atau pembayar"
                                        :class="{ 'border-red-500': errors.payee }"
                                    />
                                    <p v-if="errors.payee" class="mt-1 text-xs text-red-500">{{ errors.payee }}</p>
                                </div>

                                <!-- Amount -->
                                <div class="space-y-2">
                                    <label for="amount" class="text-sm font-medium">Jumlah</label>
                                    <Input
                                        id="amount"
                                        v-model="form.amount"
                                        type="number"
                                        placeholder="Jumlah transaksi"
                                        :class="{ 'border-red-500': errors.amount }"
                                    />
                                    <p v-if="errors.amount" class="mt-1 text-xs text-red-500">{{ errors.amount }}</p>
                                </div>

                                <!-- Date -->
                                <div class="space-y-2">
                                    <label for="date" class="text-sm font-medium">Tanggal</label>
                                    <Input id="date" v-model="form.date" type="date" :class="{ 'border-red-500': errors.date }" />
                                    <p v-if="errors.date" class="mt-1 text-xs text-red-500">{{ errors.date }}</p>
                                </div>

                                <!-- Account (Required) -->
                                <div class="space-y-2">
                                    <label for="account" class="text-sm font-medium">Rekening</label>
                                    <Select v-model="form.account_id">
                                        <SelectTrigger :class="{ 'border-red-500': errors.account_id }">
                                            <SelectValue placeholder="Pilih rekening" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="account in props.accounts" :key="account.id" :value="account.id">
                                                {{ account.name }} ({{ formatAmount(account.balance) }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="errors.account_id" class="mt-1 text-xs text-red-500">{{ errors.account_id }}</p>
                                </div>

                                <!-- Category (Optional) -->
                                <div class="space-y-2">
                                    <label for="category" class="text-sm font-medium">Kategori (Opsional)</label>
                                    <Select v-model="form.category_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih kategori (opsional)" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem :value="null">Tanpa Kategori</SelectItem>
                                            <SelectItem v-for="category in props.categories" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Memo -->
                                <div class="space-y-2">
                                    <label for="memo" class="text-sm font-medium">Memo (Opsional)</label>
                                    <Input id="memo" v-model="form.memo" placeholder="Catatan tambahan" />
                                </div>

                                <DialogFooter>
                                    <Button type="button" variant="outline" @click="showCreateDialog = false">Batal</Button>
                                    <Button type="submit">Simpan</Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Transactions Table -->
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Penerima/Pembayar</TableHead>
                                <TableHead>Kategori</TableHead>
                                <TableHead>Rekening</TableHead>
                                <TableHead>Memo</TableHead>
                                <TableHead class="text-right">Jumlah</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="transaction in props.transactions.data" :key="transaction.id">
                                <TableCell>{{ formatDate(transaction.date) }}</TableCell>
                                <TableCell>{{ transaction.payee }}</TableCell>
                                <TableCell>{{ transaction.category?.name || 'Tanpa Kategori' }}</TableCell>
                                <TableCell>
                                    {{ props.accounts.find((a) => a.id === transaction.account_id)?.name || 'Unknown' }}
                                </TableCell>
                                <TableCell>{{ transaction.memo || '-' }}</TableCell>
                                <TableCell class="text-right font-medium" :class="transaction.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatAmount(transaction.amount) }}
                                </TableCell>
                            </TableRow>

                            <!-- Empty State -->
                            <TableRow v-if="props.transactions.data.length === 0">
                                <TableCell colspan="6" class="text-muted-foreground py-6 text-center">
                                    Tidak ada transaksi untuk ditampilkan
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t p-4">
                    <div class="text-sm text-gray-600">
                        Halaman {{ props.transactions.current_page }} dari {{ props.transactions.last_page }} ({{ props.transactions.total }} total
                        transaksi)
                    </div>

                    <div class="flex space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="props.transactions.current_page === 1"
                            @click="goToPage(props.transactions.current_page - 1)"
                        >
                            <ChevronLeft class="mr-1 h-4 w-4" />
                            Sebelumnya
                        </Button>

                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="props.transactions.current_page === props.transactions.last_page"
                            @click="goToPage(props.transactions.current_page + 1)"
                        >
                            Selanjutnya
                            <ChevronRight class="ml-1 h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
