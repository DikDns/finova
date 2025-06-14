<script setup lang="ts">
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
import { ChevronRight, PlusIcon } from 'lucide-vue-next';
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
}>();

const page = usePage<SharedData>();

const getThePathOnly = (href: string) => {
    return '/' + href.split('/').slice(3).join('/');
};

// Dialog state
const showCreateAccountDialog = ref(false);
const isLoading = ref(false);

// Form data
const accountForm = ref({
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
        },
        onError: (errors) => {
            console.error('Error creating account:', errors);
            toast.error('Gagal membuat rekening');
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
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Rekening</SidebarGroupLabel>
        <SidebarMenu>
            <Collapsible v-for="item in account_types" :key="item.id" as-child :default-open="item.isActive" class="group/collapsible">
                <SidebarMenuItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton :tooltip="item.type">
                            <span class="capitalize">{{ item.type }}</span>
                            <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            <SidebarMenuSubItem v-for="subItem in item.accounts" :key="subItem.name">
                                <SidebarMenuSubButton as-child>
                                    <a :href="subItem.url" class="flex justify-between">
                                        <span class="capitalize"> {{ subItem.name }}</span>
                                        <span class="text-xs text-gray-500">{{ formatCurrency(subItem.balance, currency_code) }}</span>
                                    </a>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        </SidebarMenu>
    </SidebarGroup>
    <SidebarGroup class="px-2 py-0">
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
                    <Button type="button" variant="outline" @click="showCreateAccountDialog = false"> Batal </Button>
                    <Button type="button" @click="createAccount" :disabled="isLoading">
                        {{ isLoading ? 'Membuat...' : 'Buat Rekening' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </SidebarGroup>
</template>
