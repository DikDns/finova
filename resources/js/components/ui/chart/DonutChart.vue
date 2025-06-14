<template>
  <div class="chart-wrapper">
    <Doughnut
      :data="chartData"
      :options="chartOptions"
      :height="200"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
  type ChartOptions,
  type ChartData
} from 'chart.js';

// Register Chart.js components
ChartJS.register(ArcElement, Tooltip, Legend);

interface ExpenseData {
  category: string;
  amount: number;
  percentage: number;
  color: string;
  icon: string;
}

interface Props {
  data: ExpenseData[];
}

const props = defineProps<Props>();

const chartData = computed<ChartData<'doughnut'>>(() => ({
  labels: props.data.map(item => item.category),
  datasets: [{
    data: props.data.map(item => item.amount),
    backgroundColor: props.data.map(item => item.color),
    borderWidth: 0,
    cutout: '70%',
  }]
}));

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      callbacks: {
        label: (context) => {
          const dataIndex = context.dataIndex;
          const item = props.data[dataIndex];
          return `${item.category}: ${item.percentage}%`;
        }
      }
    }
  }
}));
</script>

<style scoped>
.chart-wrapper {
  position: relative;
  width: 100%;
  height: 300px;
}
</style>