<script setup lang="ts">
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
import Calendar from '@/components/ui/calendar/Calendar.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn, formatCurrency, formatDate } from '@/lib/utils';
import type { Account, AccountType, Budget, Category, Transaction } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDate, DateFormatter, DateValue, getLocalTimeZone } from '@internationalized/date';
import { CalendarIcon, ChevronLeft, ChevronRight, EllipsisIcon, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { AreaChart, BulletLegendItemInterface, LegendPosition } from 'vue-chrts';
import { toast } from 'vue-sonner';

interface AreaChartItem {
    date: string;
    balance: number;
    monthly_interest: number;
}

const categoriesChart: Record<string, BulletLegendItemInterface> = {
    balance: { name: 'Sisa Utang', color: '#49c1cb' },
    monthly_interest: { name: 'Bunga Bulanan', color: '#6e9fc9' },
};

const xFormatter = (i: number): string | number => {
    if (!chartData.value || !chartData.value[i]) return '';
    const date = new Date(chartData.value[i].date);
    return date ? `${date.getMonth() + 1}/${date.getFullYear()}` : '';
};

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
    current_account?: Account;
    loan_predictions: AreaChartItem[];
}

const props = defineProps<Props>();

// Form state
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const editingTransaction = ref<Transaction | null>(null);

const df = new DateFormatter('id-ID', {
    dateStyle: 'long',
});

const form = ref({
    payee: '',
    amount: 0,
    date: new Date().toISOString(),
    category_id: null as string | undefined | null,
    account_id: props.accounts.at(0)?.id ?? '',
    memo: '',
    budget_id: props.budget.id,
    type: 'expense' as 'expense' | 'income',
});

const dateValue = ref<DateValue>();

// Form errors
const errors = ref<Record<string, string>>({});
const isLoading = ref(false);
const deleteTransactionId = ref<string | null>(null);

const currentAccountName = computed(() => {
    if (props.current_account) {
        return props.current_account.name;
    }
    return 'Semua Rekening';
});

// Pagination
const goToPage = (page: number) => {
    router.get(
        route('budget.accounts.show', { budget: props.budget.id, account: props.current_account?.id }),
        {
            page: page,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

// Mengubah format data prediksi untuk AreaChart
const chartData = computed(() => {
    return props.loan_predictions.map((prediction) => ({
        date: prediction.date,
        balance: prediction.balance,
        monthly_interest: prediction.monthly_interest,
    }));
});

// Reset form
const resetForm = () => {
    dateValue.value = undefined;
    form.value = {
        payee: '',
        amount: 0,
        date: new Date().toISOString(),
        category_id: null,
        account_id: props.accounts.at(0)?.id ?? '',
        memo: '',
        budget_id: props.budget.id,
        type: 'expense',
    };
    errors.value = {};
    isLoading.value = false;
    editingTransaction.value = null;
};

// Set form for editing
const setEditForm = (transaction: Transaction) => {
    editingTransaction.value = transaction;
    dateValue.value = new CalendarDate(
        new Date(transaction.date).getFullYear(),
        new Date(transaction.date).getMonth() + 1,
        new Date(transaction.date).getDate(),
    );
    form.value = {
        payee: transaction.payee,
        amount: transaction.amount,
        date: transaction.date,
        category_id: transaction.category_id,
        account_id: transaction.payee,
        memo: transaction.memo || '',
        budget_id: props.budget.id,
        type: transaction.type || 'expense', // Gunakan tipe transaksi yang ada atau default ke 'expense'
    };
};

// Submit form
const submitForm = () => {
    isLoading.value = true;
    if (editingTransaction.value) {
        router.put(
            route('transactions.update', { transaction: editingTransaction.value.id }),
            {
                ...form.value,
                date: dateValue.value?.toString(),
                current_account_id: props.current_account?.id,
                account_id: editingTransaction.value.payee,
            },
            {
                preserveScroll: true,
                preserveState: false,
                replace: true,
                onSuccess: () => {
                    showEditDialog.value = false;
                    resetForm();
                    toast.success('Transaksi berhasil diupdate.');
                },
                onError: (err) => {
                    errors.value = err;
                    toast.error('Gagal membuat transaksi', {
                        description: errors.value.error[0],
                    });
                },
                onFinish: () => {
                    isLoading.value = false;
                },
            },
        );
    } else {
        router.post(
            route('transactions.store'),
            {
                ...form.value,
                date: dateValue.value?.toString(),
                current_account_id: props.current_account?.id,
            },
            {
                preserveScroll: true,
                preserveState: false,
                replace: true,
                onSuccess: () => {
                    showCreateDialog.value = false;
                    resetForm();
                    toast.success('Transaksi berhasil dibuat.');
                },
                onError: (err) => {
                    errors.value = err;
                    toast.error('Gagal membuat transaksi', {
                        description: errors.value.error[0],
                    });
                },
                onFinish: () => {
                    isLoading.value = false;
                },
            },
        );
    }
};

const confirmDelete = (transactionId: string) => {
    deleteTransactionId.value = transactionId;
    showDeleteDialog.value = true;
};

const handleDelete = () => {
    isLoading.value = true;
    router.delete(route('transactions.destroy', { transaction: deleteTransactionId.value }), {
        preserveScroll: true,
        preserveState: false,
        replace: true,
        onSuccess: () => {
            resetForm();
            showDeleteDialog.value = false;
            toast.success('Transaksi berhasil dihapus.');
        },
        onError: (err) => {
            toast.error('Gagal menghapus transaksi', {
                description: err.error[0],
            });
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <Head :title="currentAccountName" />

    <AppLayout :budget_id="props.budget.id" :currency_code="props.budget.currency_code" :account_types="props.account_types">
        <div class="space-y-6 p-6">
            <!-- Header  -->
            <div class="flex items-center justify-between">
                <h1 class="font-serif text-2xl font-semibold tracking-tight">{{ currentAccountName }}</h1>
            </div>

            <div class="bg-card rounded-lg border p-6 shadow-sm">
                <div class="flex items-center gap-x-3 md:gap-x-6">
                    <div>
                        <span class="text-xl font-semibold">
                            {{ formatCurrency(props.current_account?.balance ?? 0, props.budget.currency_code) }}
                        </span>
                        <p class="text-muted-foreground text-xs tracking-tight">Sisa Nominal Utang</p>
                    </div>
                    <div>
                        <span class="text-xl font-semibold">
                            {{
                                Math.round((props.current_account?.interest ?? 0) * 100)
                                    .toFixed(1)
                                    .toString()
                                    .replace('.', ',')
                            }}%
                        </span>
                        <p class="text-muted-foreground text-xs tracking-tight">Suku Bunga</p>
                    </div>
                    <div>
                        <span class="text-xl font-semibold">
                            {{ formatCurrency(props.current_account?.minimum_payment_monthly ?? 0, props.budget.currency_code) }}
                        </span>
                        <p class="text-muted-foreground text-xs tracking-tight">Minimum Pembayaran Bulanan</p>
                    </div>
                </div>
            </div>

            <div class="bg-card overflow-hidden rounded-lg border p-6 shadow-sm">
                <div class="flex items-center justify-between pb-2">
                    <h2 class="font-serif text-lg">Progres Utang</h2>
                </div>

                <AreaChart
                    :data="chartData"
                    :height="300"
                    y-label="Sisa Utang"
                    x-label="Bulan"
                    :categories="categoriesChart"
                    :y-grid-line="true"
                    :legend-position="LegendPosition.Top"
                    :hide-legend="false"
                    :x-num-ticks="8"
                    :y-num-ticks="6"
                    :x-formatter="xFormatter"
                    v-if="chartData.length !== 0"
                />
                <div v-if="chartData.length === 0" class="text-muted-foreground flex h-[300px] items-center justify-center">
                    Tidak ada data prediksi pelunasan utang
                </div>
            </div>

            <div class="bg-card overflow-hidden rounded-lg border p-6 shadow-sm">
                <div class="flex items-center justify-between border-b pb-6">
                    <h2 class="font-serif text-lg">Aktivitas</h2>

                    <!-- Create Transaction Button -->
                    <Dialog v-model:open="showCreateDialog">
                        <DialogTrigger as-child>
                            <Button class="flex items-center gap-2">
                                <Plus class="h-4 w-4" />
                                <span>Buat Transaksi</span>
                            </Button>
                        </DialogTrigger>

                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Tambah Transaksi Utang Baru</DialogTitle>
                                <DialogDescription>
                                    Isi form berikut untuk menambahkan transaksi utang baru (pembayaran atau penambahan).
                                </DialogDescription>
                            </DialogHeader>

                            <form @submit.prevent="submitForm" class="mt-4 space-y-4">
                                <!-- Date -->
                                <div class="space-y-2">
                                    <label for="date" class="text-sm font-medium">Tanggal</label>
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                variant="outline"
                                                :class="
                                                    cn(
                                                        'w-full justify-start text-left font-normal',
                                                        errors.date && 'border-red-500',
                                                        !dateValue && 'text-muted-foreground',
                                                    )
                                                "
                                            >
                                                <CalendarIcon class="mr-2 h-4 w-4" />
                                                {{ dateValue ? df.format(dateValue.toDate(getLocalTimeZone())) : 'Pilih tanggal' }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <Calendar v-model="dateValue" initial-focus />
                                        </PopoverContent>
                                    </Popover>
                                    <p v-if="errors.date" class="mt-1 text-xs text-red-500">{{ errors.date }}</p>
                                </div>

                                <!-- Account (Required) -->
                                <div class="space-y-2">
                                    <label for="account" class="text-sm font-medium">Rekening</label>
                                    <Select v-model="form.account_id">
                                        <SelectTrigger :class="{ 'border-red-500': errors.account_id, 'w-full': true }">
                                            <SelectValue placeholder="Pilih rekening" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="account in props.accounts" :key="account.id" :value="account.id">
                                                {{ account.name }} ({{ formatCurrency(account.balance, props.budget.currency_code) }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="errors.account_id" class="mt-1 text-xs text-red-500">{{ errors.account_id }}</p>
                                </div>

                                <!-- Amount -->
                                <div class="space-y-2">
                                    <Label for="amount" class="text-sm font-medium"> Jumlah Transaksi</Label>
                                    <NumberField
                                        :step="500"
                                        :min="0"
                                        id="amount"
                                        placeholder="Jumlah transaksi"
                                        :model-value="form.amount"
                                        :format-options="{
                                            style: 'currency',
                                            currency: props.budget.currency_code,
                                            currencyDisplay: 'code',
                                            currencySign: 'accounting',
                                        }"
                                        @update:model-value="
                                            (v) => {
                                                if (v) {
                                                    form.amount = v;
                                                } else {
                                                    form.amount = 0;
                                                }
                                            }
                                        "
                                    >
                                        <NumberFieldContent>
                                            <NumberFieldDecrement />
                                            <NumberFieldInput />
                                            <NumberFieldIncrement />
                                        </NumberFieldContent>
                                    </NumberField>
                                    <p v-if="errors.amount" class="mt-1 text-xs text-red-500">{{ errors.amount }}</p>
                                </div>

                                <!-- Transaction Type -->
                                <div class="space-y-2">
                                    <label for="type" class="text-sm font-medium">Tipe Transaksi</label>
                                    <Select v-model="form.type">
                                        <SelectTrigger :class="{ 'border-red-500': errors.type, 'w-full': true }">
                                            <SelectValue placeholder="Pilih tipe transaksi" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="expense">Pembayaran Utang</SelectItem>
                                            <SelectItem value="income">Penambahan Utang</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="errors.type" class="mt-1 text-xs text-red-500">{{ errors.type }}</p>
                                </div>

                                <!-- Memo -->
                                <div class="space-y-2">
                                    <label for="memo" class="text-sm font-medium">Memo (Opsional)</label>
                                    <Input id="memo" v-model="form.memo" placeholder="Catatan tambahan" />
                                </div>

                                <DialogFooter>
                                    <Button :disabled="isLoading" type="button" variant="outline" @click="showCreateDialog = false">Batal</Button>
                                    <Button :disabled="isLoading" type="submit">Simpan</Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <Dialog
                    v-model:open="showEditDialog"
                    @update:open="
                        (open) => {
                            if (!open) {
                                resetForm();
                            }
                        }
                    "
                >
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>No</TableHead>
                                    <TableHead>Tanggal</TableHead>
                                    <TableHead>Rekening</TableHead>
                                    <TableHead>Memo</TableHead>
                                    <TableHead class="text-right">Jumlah</TableHead>
                                    <TableHead></TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody v-auto-animate>
                                <TableRow v-for="(transaction, index) in props.transactions.data" :key="transaction.id">
                                    <TableCell class="text-center">{{
                                        index + 1 + (props.transactions.current_page - 1) * props.transactions.per_page
                                    }}</TableCell>
                                    <TableCell>{{ formatDate(transaction.date) }}</TableCell>
                                    <TableCell>
                                        {{ props.accounts.find((a) => a.id === transaction.payee)?.name || '-' }}
                                    </TableCell>
                                    <TableCell>{{ transaction.memo || '-' }}</TableCell>
                                    <TableCell
                                        :class="cn('text-right font-medium', transaction.type === 'expense' ? 'text-green-600' : 'text-red-600')"
                                    >
                                        {{ transaction.type === 'expense' ? '-' : '+'
                                        }}{{ formatCurrency(Math.abs(transaction.amount), props.budget.currency_code) }}
                                    </TableCell>
                                    <TableCell>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button :disabled="isLoading" size="icon" variant="ghost">
                                                    <EllipsisIcon class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent>
                                                <DialogTrigger as-child>
                                                    <DropdownMenuItem @click="setEditForm(transaction)"> Ubah </DropdownMenuItem>
                                                </DialogTrigger>
                                                <DropdownMenuItem @click="() => confirmDelete(transaction.id)">Hapus</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>

                                <!-- Empty State -->
                                <TableRow v-if="props.transactions.data.length === 0">
                                    <TableCell colspan="6" class="text-muted-foreground py-6 text-center">
                                        Tidak ada aktivitas untuk ditampilkan
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Ubah Transaksi</DialogTitle>
                            <DialogDescription> Isi form berikut untuk mengubah transaksi. </DialogDescription>
                        </DialogHeader>

                        <form @submit.prevent="submitForm" class="mt-4 space-y-4">
                            <!-- Date -->
                            <div class="space-y-2">
                                <label for="date" class="text-sm font-medium">Tanggal</label>
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            :class="
                                                cn(
                                                    'w-full justify-start text-left font-normal',
                                                    errors.date && 'border-red-500',
                                                    !dateValue && 'text-muted-foreground',
                                                )
                                            "
                                        >
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ dateValue ? df.format(dateValue.toDate(getLocalTimeZone())) : 'Pick a date' }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar
                                            :model-value="dateValue"
                                            calendar-label="Tanggal transaksi"
                                            initial-focus
                                            @update:model-value="
                                                (v) => {
                                                    if (v) {
                                                        form.date = v.toDate(getLocalTimeZone()).toISOString();
                                                        dateValue = v;
                                                    } else {
                                                        form.date = '';
                                                        dateValue = undefined;
                                                    }
                                                }
                                            "
                                        />
                                    </PopoverContent>
                                </Popover>
                                <p v-if="errors.date" class="mt-1 text-xs text-red-500">{{ errors.date }}</p>
                            </div>

                            <div class="space-y-2">
                                <label for="account" class="text-sm font-medium">Rekening</label>
                                <Select v-model="form.payee">
                                    <SelectTrigger :class="{ 'border-red-500': errors.payee, 'w-full': true }">
                                        <SelectValue placeholder="Pilih rekening" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="account in props.accounts" :key="account.id" :value="account.id">
                                            {{ account.name }} ({{ formatCurrency(account.balance) }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.payee" class="mt-1 text-xs text-red-500">{{ errors.payee }}</p>
                            </div>

                            <!-- Amount -->
                            <div class="space-y-2">
                                <Label for="amount" class="text-sm font-medium"> Jumlah transaksi</Label>
                                <NumberField
                                    :step="500"
                                    :min="0"
                                    id="amount"
                                    placeholder="Jumlah transaksi"
                                    :model-value="form.amount"
                                    :format-options="{
                                        style: 'currency',
                                        currency: props.budget.currency_code,
                                        currencyDisplay: 'code',
                                        currencySign: 'accounting',
                                    }"
                                    @update:model-value="
                                        (v) => {
                                            if (v) {
                                                form.amount = v;
                                            } else {
                                                form.amount = 0;
                                            }
                                        }
                                    "
                                >
                                    <NumberFieldContent>
                                        <NumberFieldDecrement />
                                        <NumberFieldInput />
                                        <NumberFieldIncrement />
                                    </NumberFieldContent>
                                </NumberField>
                                <p v-if="errors.amount" class="mt-1 text-xs text-red-500">{{ errors.amount }}</p>
                            </div>

                            <!-- Transaction Type -->
                            <div class="space-y-2">
                                <label for="type" class="text-sm font-medium">Tipe Transaksi</label>
                                <Select v-model="form.type">
                                    <SelectTrigger :class="{ 'border-red-500': errors.type, 'w-full': true }">
                                        <SelectValue placeholder="Pilih tipe transaksi" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="expense">Pembayaran Utang (Expense)</SelectItem>
                                        <SelectItem value="income">Penambahan Utang (Income)</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.type" class="mt-1 text-xs text-red-500">{{ errors.type }}</p>
                            </div>

                            <!-- Memo -->
                            <div class="space-y-2">
                                <label for="memo" class="text-sm font-medium">Memo (Opsional)</label>
                                <Input id="memo" v-model="form.memo" placeholder="Catatan tambahan" />
                            </div>

                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="
                                        showEditDialog = false;
                                        resetForm();
                                    "
                                    :disabled="isLoading"
                                    >Batal</Button
                                >
                                <Button type="submit" :disabled="isLoading">Simpan</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t pt-6">
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

        <!-- Delete Confirmation Dialog -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Apakah Anda yakin?</AlertDialogTitle>
                    <AlertDialogDescription> Apakah Anda yakin ingin menghapus pembayaran ini? </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel :disabled="isLoading" @click="showDeleteDialog = false">Batal</AlertDialogCancel>
                    <AlertDialogAction :disabled="isLoading" @click="handleDelete" variant="destructive">Hapus</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
