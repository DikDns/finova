<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { AccountType, Budget } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    budget: Budget;
    account_types: AccountType[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Analisis Keuangan" />

    <AppLayout :budget_id="props.budget.id" :currency_code="props.budget.currency_code" :account_types="props.account_types">
        <div class="p-6">
            <!-- Header  -->
            <div class="flex items-center justify-between">
                <h1 class="font-serif text-2xl font-semibold tracking-tight">Halaman Analisis</h1>
            </div>
        </div>
    </AppLayout>
</template>
