<script setup lang="ts">
interface Props {
    amount: string;
    currencyCode: string;
}

 defineProps<Props>();

// Format currency
const formatCurrency = (amount: number, currencyCode = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <div class="bg-card rounded-lg border p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-2">
                    <div class="h-2 w-2 rounded-full bg-green-500"></div>
                    <span class="text-xl font-semibold">{{ formatCurrency(parseFloat(amount), currencyCode) }}</span>
                </div>
                <p class="text-muted-foreground text-sm">Siap dialokasikan</p>
            </div>
        </div>
    </div>
</template>