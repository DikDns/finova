<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type Budget } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    budget: Budget;
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Semua Rekening" />

    <AppLayout :budget_id="props.budget.id">
        <div class="p-6">
            <!-- Header  -->
            <div class="flex items-center justify-between">
                <h1 class="font-serif text-2xl font-semibold tracking-tight">Halaman Semua Rekening</h1>
            </div>
        </div>
    </AppLayout>
</template>
