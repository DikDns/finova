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
    balance: '',
    interest: '',
    minimum_payment_monthly: '',
});

const editAccountForm = ref({
    id: '',
    name: '',
    type: '',
    balance: '',
    interest: '',
    minimum_payment_monthly: '',
});

const resetForm = () => {
    accountForm.value = {
        name: '',
        type: '',
        balance: '',
        interest: '',
        minimum_payment_monthly: '',
    };
};

const resetEditForm = () => {
    editAccountForm.value = {
        id: '',
        name: '',
        type: '',
        balance: '',
        interest: '',
        minimum_payment_monthly: '',
    };
};

const openEditDialog = (account: any, accountType: string) => {
    editingAccount.value = account;
    editAccountForm.value = {
        id: account.id,
        name: account.name,
        type: accountType,
        balance: account.balance.toString(),
        interest: account.interest?.toString() || '',
        minimum_payment_monthly: account.minimum_payment_monthly?.toString() || '',
    };
    showEditAccountDialog.value = true;
};

const createAccount = () => {
    if (!accountForm.value.name || !accountForm.value.type || !accountForm.value.balance) {
        toast.error('Mohon lengkapi semua field yang diperlukan');
        return;
    }

    isLoading.value = true;

    const formData: any = {
        name: accountForm.value.name,
        type: accountForm.value.type,
        balance: parseFloat(accountForm.value.balance),
        budget_id: props.budget_id,
    };

    if (accountForm.value.type === 'loan') {
        formData.interest = parseFloat(accountForm.value.interest) || 0;
        formData.minimum_payment_monthly = parseFloat(accountForm.value.minimum_payment_monthly) || 0;
    }

    router.post(route('accounts.store'), formData, {
        onSuccess: () => {
            showCreateAccountDialog.value = false;
            resetForm();
            toast.success('Rekening berhasil dibuat');
            router.visit(window.location.href, {
                preserveScroll: true,
                preserveState: false,
            });
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
    if (!editAccountForm.value.name || !editAccountForm.value.type || !editAccountForm.value.balance) {
        toast.error('Mohon lengkapi semua field yang diperlukan');
        return;
    }

    isLoading.value = true;

    const formData: any = {
        name: editAccountForm.value.name,
        type: editAccountForm.value.type,
        balance: parseFloat(editAccountForm.value.balance),
        budget_id: props.budget_id,
    };

    if (editAccountForm.value.type === 'loan') {
        formData.interest = parseFloat(editAccountForm.value.interest) || 0;
        formData.minimum_payment_monthly = parseFloat(editAccountForm.value.minimum_payment_monthly) || 0;
    }

    router.put(route('accounts.update', editAccountForm.value.id), formData, {
        onSuccess: () => {
            showEditAccountDialog.value = false;
            resetEditForm();
            toast.success('Rekening berhasil diperbarui');
            router.visit(window.location.href, {
                preserveScroll: true,
                preserveState: false,
            });
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
        onSuccess: () => {
            showEditAccountDialog.value = false;
            resetEditForm();
            toast.success('Rekening berhasil dihapus');
            router.visit(window.location.href, {
                preserveScroll: true,
                preserveState: false,
            });
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
        <SidebarGroupLabel>Utama</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <!-- The item.href is contain the domain remove it -->
                <SidebarMenuButton as-child :is-active="getThePathOnly(item.href) === page.url" :tooltip="item.title">
                    <Link :href="item.href">
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
                                        <a :href="subItem.url" class="flex min-w-0 flex-1 justify-between">
                                            <span class="truncate capitalize"> {{ subItem.name }}</span>
                                            <span class="ml-2 text-xs text-gray-500">{{ formatCurrency(subItem.balance, currency_code) }}</span>
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
                        <Input id="name" v-model="accountForm.name" placeholder="Nama rekening" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="type" class="text-right"> Jenis </Label>
                        <Select v-model="accountForm.type">
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
                        <Label for="balance" class="text-right">
                            {{ accountForm.type === 'loan' ? 'Total Utang' : 'Saldo' }}
                        </Label>
                        <Input
                            id="balance"
                            v-model="accountForm.balance"
                            type="number"
                            step="500"
                            :placeholder="accountForm.type === 'loan' ? 'Jumlah utang' : 'Saldo awal'"
                            class="col-span-3"
                        />
                    </div>
                    <!-- Additional fields for loan type -->
                    <template v-if="accountForm.type === 'loan'">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="interest" class="text-right"> Bunga/Bulan (%) </Label>
                            <Input
                                id="interest"
                                v-model="accountForm.interest"
                                type="number"
                                step="1"
                                max="100"
                                min="0"
                                placeholder="Bunga per bulan"
                                class="col-span-3"
                            />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="minimum_payment" class="text-right"> Bayar Minimum </Label>
                            <Input
                                id="minimum_payment"
                                v-model="accountForm.minimum_payment_monthly"
                                type="number"
                                step="500"
                                placeholder="Pembayaran minimum bulanan"
                                class="col-span-3"
                            />
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
                        <Input id="edit-name" v-model="editAccountForm.name" placeholder="Nama rekening" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-type" class="text-right"> Jenis </Label>
                        <Select v-model="editAccountForm.type">
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
                        <Label for="edit-balance" class="text-right">
                            {{ editAccountForm.type === 'loan' ? 'Total Utang' : 'Saldo' }}
                        </Label>
                        <Input
                            id="edit-balance"
                            v-model="editAccountForm.balance"
                            type="number"
                            step="500"
                            :placeholder="editAccountForm.type === 'loan' ? 'Jumlah utang' : 'Saldo awal'"
                            class="col-span-3"
                        />
                    </div>
                    <!-- Additional fields for loan type -->
                    <template v-if="editAccountForm.type === 'loan'">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="edit-interest" class="text-right"> Bunga/Bulan (%) </Label>
                            <Input
                                id="edit-interest"
                                v-model="editAccountForm.interest"
                                type="number"
                                step="1"
                                max="100"
                                min="0"
                                placeholder="Bunga per bulan"
                                class="col-span-3"
                            />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="edit-minimum-payment" class="text-right"> Bayar Minimum </Label>
                            <Input
                                id="edit-minimum-payment"
                                v-model="editAccountForm.minimum_payment_monthly"
                                type="number"
                                step="500"
                                placeholder="Pembayaran minimum bulanan"
                                class="col-span-3"
                            />
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
