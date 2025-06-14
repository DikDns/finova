<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import DonutChart from '@/components/ui/chart/DonutChart.vue';
import ButtonAi from '@/components/ui/button/ButtonAi.vue';
import { type Budget } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    budget: Budget;
}

const props = defineProps<Props>();

// Sample data 
const expenseData = ref([
  {
    category: 'KPR',
    amount: 250000,
    percentage: 77,
    color: '#2563eb',
    icon: '🏠'
  },
  {
    category: 'Listrik',
    amount: 100000,
    percentage: 22,
    color: '#16a34a',
    icon: '⚡'
  },
  {
    category: 'Belanja',
    amount: 150000,
    percentage: 34,
    color: '#dc2626',
    icon: '🛒'
  },
  {
    category: 'Bensin',
    amount: 80000,
    percentage: 22,
    color: '#ea580c',
    icon: '⛽'
  }
]);

const totalExpense = computed(() => {
  return expenseData.value.reduce((total, item) => total + item.amount, 0);
});

const monthlyAverage = computed(() => {
  return Math.round(totalExpense.value / 12);
});

const dailyAverage = computed(() => {
  return Math.round(totalExpense.value / 30);
});

const mostActiveCategory = computed(() => {
  return expenseData.value.reduce((prev, current) => 
    prev.percentage > current.percentage ? prev : current
  );
});

const largestExpense = computed(() => {
  return Math.max(...expenseData.value.map(item => item.amount));
});

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount);
};

// AI Assistant functionality
const handleAIAssistant = () => {
  // Handle AI Assistant logic here
  console.log('AI Assistant clicked');
};
</script>

<template>
    <Head title="Analisis Keuangan" />

    <AppLayout :budget_id="props.budget.id">
        <div class="analysis-page">
            <!-- Header -->
            <div class="header">
                <h1 class="title">Analisa</h1>
                <ButtonAi 
                    @click="handleAIAssistant"
                    class="ai-assistant-btn"
                >
                    AI Assistant
                </ButtonAi>
            </div>

            <!-- Analysis Container -->
            <div class="analysis-container">
                <!-- Total Expense Chart -->
                <div class="chart-container">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title">Total Pengeluaran</h3>
                            <p class="chart-subtitle">{{ formatCurrency(totalExpense) }}</p>
                        </div>
                    </div>
                    
                    <DonutChart :data="expenseData" />
                    
                    <!-- Chart Legend -->
                    <div class="chart-legend">
                        <div 
                            v-for="item in expenseData" 
                            :key="item.category"
                            class="legend-item"
                        >
                            <div 
                                class="legend-color" 
                                :style="{ backgroundColor: item.color }"
                            ></div>
                            <span class="legend-text">{{ item.category }}</span>
                        </div>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Rerata Pengeluaran Bulanan</div>
                            <div class="stat-value">{{ formatCurrency(monthlyAverage) }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Rerata Pengeluaran Harian</div>
                            <div class="stat-value">{{ formatCurrency(dailyAverage) }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Kategori Paling Aktif</div>
                            <div class="stat-value">{{ mostActiveCategory.icon }} {{ mostActiveCategory.category }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Pengeluaran Terbesar</div>
                            <div class="stat-value">{{ formatCurrency(largestExpense) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="chart-container">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title">Kategori Pengeluaran</h3>
                            <p class="chart-subtitle">Berdasarkan Persentase</p>
                        </div>
                    </div>
                    
                    <div class="category-list">
                        <div 
                            v-for="item in expenseData" 
                            :key="item.category"
                            class="category-item"
                        >
                            <div class="category-info">
                                <span class="category-icon">{{ item.icon }}</span>
                                <span class="category-name">{{ item.category }}</span>
                            </div>
                            <div class="category-amounts">
                                <span class="category-amount">{{ formatCurrency(item.amount) }}</span>
                                <span class="category-percentage">{{ item.percentage }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.analysis-page {
    padding: 1.5rem;
    min-height: 100vh;
    background-color: #f8fafc;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
}


.ai-assistant-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.analysis-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.chart-container {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-header {
    margin-bottom: 1.5rem;
}

.chart-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.chart-subtitle {
    font-size: 1.875rem;
    font-weight: 700;
    color: #059669;
}

.chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin: 1rem 0;
    justify-content: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
}

.legend-text {
    font-size: 0.875rem;
    color: #6b7280;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-top: 1.5rem;
}

.stat-card {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    text-align: center;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
}

.category-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.category-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.5rem;
}

.category-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.category-icon {
    font-size: 1.25rem;
}

.category-name {
    font-weight: 500;
    color: #1f2937;
}

.category-amounts {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}

.category-amount {
    font-weight: 600;
    color: #1f2937;
}

.category-percentage {
    font-size: 0.875rem;
    color: #6b7280;
}


/* Responsive */
@media (max-width: 768px) {
    .analysis-container {
        grid-template-columns: 1fr;
    }
    
    .ai-assistant-sidebar {
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>