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
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { formatCurrency } from '@/lib/utils';
import { type NavItem, type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronRight, Edit, PlusIcon, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    items: NavItem[];
    currency_code: string;
    budget_id: string;
    account_types: {
        id: string;
        type: string;
        isActive: boolean;
        accounts: {
            id: string;
            name: string;
            url: string;
            balance: number;
        }[];
    }[];
    isCollapsed?: boolean;
}>();

const page = usePage<SharedData>();

const getThePathOnly = (href: string) => {
    return '/' + href.split('/').slice(3).join('/');
};

// Dialog state
const showCreateAccountDialog = ref(false);
const showEditAccountDialog = ref(false);
const showDeleteConfirmDialog = ref(false);
const isLoading = ref(false);
const editingAccount = ref<any>(null);

// Form data
const accountForm = ref({
    name: '',
    type: '',
    balance: 0,
    interest: 0.05,
    minimum_payment_monthly: 0,
});

const editAccountForm = ref({
    id: '',
    name: '',
    type: '',
    balance: 0,
    interest: 0.05,
    minimum_payment_monthly: 0,
});

const resetForm = () => {
    accountForm.value = {
        name: '',
        type: '',
        balance: 0,
        interest: 0.05,
        minimum_payment_monthly: 0,
    };
};

const resetEditForm = () => {
    editAccountForm.value = {
        id: '',
        name: '',
        type: '',
        balance: 0,
        interest: 0.05,
        minimum_payment_monthly: 0,
    };
};

const openEditDialog = (account: any, accountType: string) => {
    editingAccount.value = account;
    editAccountForm.value = {
        id: account.id,
        name: account.name,
        type: accountType,
        balance: account.balance,
        interest: account.interest ?? 0.05,
        minimum_payment_monthly: account.minimum_payment_monthly ?? 0,
    };
    showEditAccountDialog.value = true;
};

const createAccount = () => {
    if (!accountForm.value.name || !accountForm.value.type) {
        toast.error('Mohon lengkapi semua field yang diperlukan');
        return;
    }

    if (accountForm.value.balance < 0) {
        toast.error(`Jumlah ${accountForm.value.type === 'cash' ? 'saldo' : 'utang'} tidak boleh negatif`);
        return;
    }

    isLoading.value = true;

    const formData: any = {
        name: accountForm.value.name,
        type: accountForm.value.type,
        balance: accountForm.value.balance,
        budget_id: props.budget_id,
    };

    if (accountForm.value.type === 'loan') {
        formData.interest = accountForm.value.interest;
        formData.minimum_payment_monthly = accountForm.value.minimum_payment_monthly;
    }

    router.post(route('accounts.store'), formData, {
        preserveScroll: true,
        preserveState: false,
        replace: true,
        onSuccess: () => {
            showCreateAccountDialog.value = false;
            resetForm();
            toast.success('Rekening berhasil dibuat');
        },
        onError: (errors) => {
            console.error('Error creating account:', errors);
            toast.error('Gagal membuat rekening', {
                description: errors.error[0],
            });
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const updateAccount = () => {
    console.log('editAccountForm: ', editAccountForm.value);

    if (!editAccountForm.value.name || !editAccountForm.value.type) {
        toast.error('Mohon lengkapi semua field yang diperlukan');
        return;
    }

    if (editAccountForm.value.balance < 0) {
        toast.error(`Jumlah ${editAccountForm.value.type === 'cash' ? 'saldo' : 'utang'} tidak boleh negatif`);
        return;
    }

    isLoading.value = true;

    const formData: any = {
        name: editAccountForm.value.name,
        type: editAccountForm.value.type,
        balance: editAccountForm.value.balance,
        budget_id: props.budget_id,
    };

    if (editAccountForm.value.type === 'loan') {
        formData.interest = editAccountForm.value.interest;
        formData.minimum_payment_monthly = editAccountForm.value.minimum_payment_monthly;
    }

    router.put(route('accounts.update', editAccountForm.value.id), formData, {
        preserveScroll: true,
        preserveState: false,
        replace: true,
        onSuccess: () => {
            showEditAccountDialog.value = false;
            resetEditForm();
            toast.success('Rekening berhasil diperbarui');
        },
        onError: (errors) => {
            console.error('Error updating account:', errors);
            toast.error('Gagal memperbarui rekening', {
                description: errors.error[0],
            });
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const deleteAccount = () => {
    if (!editAccountForm.value.id) {
        toast.error('ID rekening tidak valid');
        return;
    }

    showDeleteConfirmDialog.value = true;
};

const confirmDeleteAccount = () => {
    isLoading.value = true;
    showDeleteConfirmDialog.value = false;

    router.delete(route('accounts.destroy', editAccountForm.value.id), {
        preserveScroll: true,
        preserveState: false,
        replace: true,
        onSuccess: () => {
            showEditAccountDialog.value = false;
            resetEditForm();
            toast.success('Rekening berhasil dihapus');
        },
        onError: (errors) => {
            console.error('Error deleting account:', errors);
            toast.error('Gagal menghapus rekening', {
                description: errors.error[0] ?? 'Terjadi kesalahan saat menghapus rekening',
            });
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Dashboard</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <!-- The item.href is contain the domain remove it -->
                <SidebarMenuButton
                    as-child
                    :is-active="getThePathOnly(item.href) === page.url"
                    :tooltip="item.title"
                    v-if="!((item.title === 'Semua Rekening' || item.title === 'Analisis') && account_types.length === 0)"
                >
                    <Link :href="item.href" prefetch>
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
    <SidebarGroup class="px-2 py-0" v-show="!isCollapsed">
        <SidebarGroupLabel>Rekening</SidebarGroupLabel>
        <SidebarMenu>
            <Collapsible v-for="item in account_types" :key="item.id" as-child :default-open="item.isActive" class="group/collapsible">
                <SidebarMenuItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton :tooltip="item.type" class="cursor-pointer">
                            <span class="capitalize">{{ item.type }}</span>
                            <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub class="mr-0 pr-0">
                            <SidebarMenuSubItem v-for="subItem in item.accounts" :key="subItem.name" class="group/account">
                                <SidebarMenuSubButton as-child>
                                    <div class="flex w-full items-center justify-between">
                                        <a :href="subItem.url" class="flex min-w-0 flex-1 items-center justify-between">
                                            <span class="truncate capitalize"> {{ subItem.name }}</span>
                                            <span
                                                class="ml-2 text-xs text-gray-500"
                                                v-if="item.type === 'cash' || (item.type === 'loan' && subItem.balance === 0)"
                                            >
                                                {{ formatCurrency(subItem.balance, currency_code) }}
                                            </span>
                                            <span class="ml-2 text-xs text-gray-500" v-else>
                                                -{{ formatCurrency(subItem.balance, currency_code) }}
                                            </span>
                                        </a>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="ml-1 h-6 w-6 flex-shrink-0 p-0 opacity-0 transition-opacity group-hover/account:opacity-100"
                                            @click.prevent="openEditDialog(subItem, item.type)"
                                        >
                                            <Edit class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        </SidebarMenu>
    </SidebarGroup>
    <SidebarGroup class="px-2 py-0" v-show="!isCollapsed">
        <Dialog v-model:open="showCreateAccountDialog">
            <DialogTrigger as-child>
                <Button variant="ghost" class="mt-2" @click="resetForm">
                    <PlusIcon class="h-4 w-4" />
                    <span>Buat Rekening</span>
                </Button>
            </DialogTrigger>
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Buat Rekening Baru</DialogTitle>
                    <DialogDescription> Pilih jenis rekening dan isi informasi yang diperlukan. </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="name" class="text-right"> Nama </Label>
                        <Input id="name" v-model="accountForm.name" placeholder="Nama rekening" class="col-span-3" required />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="type" class="text-right"> Jenis </Label>
                        <Select v-model="accountForm.type" default-value="cash">
                            <SelectTrigger class="col-span-3 w-full">
                                <SelectValue placeholder="Pilih jenis rekening" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="cash">Cash (Kas, Tabungan)</SelectItem>
                                <SelectItem value="loan">Loan (Utang)</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="balance" class="text-right"> {{ accountForm.type === 'loan' ? 'Total Utang' : 'Saldo' }} </Label>
                        <NumberField
                            id="balance"
                            :step="500"
                            :min="0"
                            :model-value="accountForm.balance"
                            :format-options="{
                                style: 'currency',
                                currency: currency_code,
                                currencyDisplay: 'code',
                                currencySign: 'accounting',
                            }"
                            class="col-span-3"
                            @update:model-value="
                                (v) => {
                                    if (v) {
                                        accountForm.balance = v;
                                    } else {
                                        accountForm.balance = 0;
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
                    </div>
                    <!-- Additional fields for loan type -->
                    <template v-if="accountForm.type === 'loan'">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="interest" class="text-right"> Bunga/Bulan </Label>
                            <NumberField
                                id="interest"
                                :model-value="accountForm.interest"
                                :default-value="0.05"
                                :step="0.01"
                                :format-options="{
                                    style: 'percent',
                                }"
                                class="col-span-3"
                                @update:model-value="
                                    (v) => {
                                        if (v) {
                                            accountForm.interest = v;
                                        } else {
                                            accountForm.interest = 0;
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
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="minimum_payment" class="text-right"> Bayar Minimum </Label>
                            <NumberField
                                id="minimum_payment"
                                :step="500"
                                :min="0"
                                :model-value="accountForm.minimum_payment_monthly"
                                :format-options="{
                                    style: 'currency',
                                    currency: currency_code,
                                    currencyDisplay: 'code',
                                    currencySign: 'accounting',
                                }"
                                class="col-span-3"
                                @update:model-value="
                                    (v) => {
                                        if (v) {
                                            accountForm.minimum_payment_monthly = v;
                                        } else {
                                            accountForm.minimum_payment_monthly = 0;
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
                        </div>
                    </template>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showCreateAccountDialog = false" :disabled="isLoading"> Batal </Button>
                    <Button type="button" @click="createAccount" :disabled="isLoading">
                        {{ isLoading ? 'Membuat...' : 'Buat Rekening' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Account Dialog -->
        <Dialog v-model:open="showEditAccountDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Edit Rekening</DialogTitle>
                    <DialogDescription> Perbarui informasi rekening Anda. </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-name" class="text-right"> Nama </Label>
                        <Input id="edit-name" v-model="editAccountForm.name" placeholder="Nama rekening" class="col-span-3" required />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4" v-if="editAccountForm.type !== 'loan'">
                        <Label for="edit-balance" class="text-right"> Saldo </Label>
                        <NumberField
                            class="col-span-3 text-left"
                            id="edit-balance"
                            :step="500"
                            :min="0"
                            :model-value="editAccountForm.balance"
                            :format-options="{
                                style: 'currency',
                                currency: currency_code,
                                currencyDisplay: 'code',
                                currencySign: 'accounting',
                            }"
                            @update:model-value="
                                (v) => {
                                    if (v) {
                                        editAccountForm.balance = v;
                                    } else {
                                        editAccountForm.balance = 0;
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
                    </div>
                    <!-- Additional fields for loan type -->
                    <template v-if="editAccountForm.type === 'loan'">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="interest" class="text-right"> Bunga/Bulan </Label>
                            <NumberField
                                id="interest"
                                :model-value="editAccountForm.interest"
                                :default-value="0.05"
                                :step="0.01"
                                :format-options="{
                                    style: 'percent',
                                }"
                                class="col-span-3"
                                @update:model-value="
                                    (v) => {
                                        if (v) {
                                            editAccountForm.interest = v;
                                        } else {
                                            editAccountForm.interest = 0;
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
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="minimum_payment" class="text-right"> Bayar Minimum </Label>
                            <NumberField
                                id="minimum_payment"
                                :step="500"
                                :min="0"
                                :model-value="editAccountForm.minimum_payment_monthly"
                                :format-options="{
                                    style: 'currency',
                                    currency: currency_code,
                                    currencyDisplay: 'code',
                                    currencySign: 'accounting',
                                }"
                                class="col-span-3"
                                @update:model-value="
                                    (v) => {
                                        if (v) {
                                            editAccountForm.minimum_payment_monthly = v;
                                        } else {
                                            editAccountForm.minimum_payment_monthly = 0;
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
                        </div>
                    </template>
                </div>
                <DialogFooter class="flex justify-between">
                    <Button type="button" variant="destructive" @click="deleteAccount" :disabled="isLoading" class="mr-auto">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Hapus
                    </Button>
                    <div class="flex gap-2">
                        <Button type="button" variant="outline" @click="showEditAccountDialog = false" :disabled="isLoading"> Batal </Button>
                        <Button type="button" @click="updateAccount" :disabled="isLoading"> Simpan </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog v-model:open="showDeleteConfirmDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Konfirmasi Hapus Rekening</AlertDialogTitle>
                    <AlertDialogDescription>
                        Apakah Anda yakin ingin menghapus rekening ini? Aksi ini tidak dapat dibatalkan. Rekening dapat dihapus jika rekening tidak
                        memiliki transaksi.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDeleteAccount" variant="destructive"> Hapus Rekening </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </SidebarGroup>
</template>
