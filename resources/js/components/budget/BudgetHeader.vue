<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    budgetName: string;
    currentMonth: Date;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    prevMonth: [];
    nextMonth: [];
}>();

// Format month
const formattedMonth = computed(() => {
    return props.currentMonth.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
});

const goToPrevMonth = () => {
    emit('prevMonth');
};

const goToNextMonth = () => {
    emit('nextMonth');
};
</script>

<template>
    <div class="flex items-center justify-between">
        <h1 class="font-serif text-2xl font-semibold tracking-tight">{{ budgetName }}</h1>

        <div class="flex items-center space-x-2">
            <Button variant="outline" size="icon" @click="goToPrevMonth">
                <ChevronLeft class="h-4 w-4" />
            </Button>
            <span class="text-sm font-medium">{{ formattedMonth }}</span>
            <Button variant="outline" size="icon" @click="goToNextMonth">
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>