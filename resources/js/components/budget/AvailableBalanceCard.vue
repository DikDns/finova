<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

interface Props {
    amount: number;
    currencyCode?: string;
}

const props = defineProps<Props>();

// Format currency
const formatCurrency = (amount: number, currencyCode = props.currencyCode || 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Emit events for allocating funds
const emit = defineEmits(['allocate']);

const allocateFunds = () => {
    emit('allocate');
};

// Compute percentage for visual indicator
const percentFilled = computed(() => {
    // This is a placeholder. In a real implementation, you might
    // calculate this based on total budget vs available amount
    return Math.min(100, Math.max(0, (props.amount / 10000000) * 100));
});
</script>

<template>
    <div class="bg-card rounded-lg border p-6 shadow-sm overflow-hidden relative">
        <!-- Background gradient indicator -->
        <div 
            class="absolute bottom-0 left-0 w-full h-1.5 bg-muted overflow-hidden rounded-b-lg"
        >
            <div 
                class="h-full bg-gradient-to-r from-green-400 to-emerald-500 transition-all duration-500 ease-in-out" 
                :style="{ width: `${percentFilled}%` }"
            ></div>
        </div>

        <div class="flex items-center justify-between">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                        <div class="h-3 w-3 rounded-full bg-primary animate-pulse"></div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight">{{ formatCurrency(props.amount) }}</h3>
                        <p class="text-muted-foreground text-sm">Siap dialokasikan</p>
                    </div>
                </div>
            </div>
            <Button class="px-6" @click="allocateFunds">Alokasikan</Button>
        </div>
    </div>
</template>