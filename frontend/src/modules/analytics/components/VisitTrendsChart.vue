<template>
  <div class="chart-card">
    <div class="chart-header">
      <h3 class="chart-title">Tren Kunjungan Pasien</h3>
      <select :value="analyticsStore.period" @change="handlePeriodChange" class="period-select">
        <option value="week">Minggu ini</option>
        <option value="month">Bulan ini</option>
        <option value="year">Tahun ini</option>
      </select>
    </div>
    
    <div class="chart-container">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import { Chart, registerables } from 'chart.js'
import { useAnalyticsStore } from '../stores/analyticsStore'

Chart.register(...registerables)

const props = defineProps({
  data: { type: Array, required: true },
})

const analyticsStore = useAnalyticsStore()
const chartCanvas = ref(null)
let chartInstance = null

const handlePeriodChange = (e) => {
  analyticsStore.setPeriod(e.target.value)
}

const createChart = () => {
  if (!chartCanvas.value) return
  if (chartInstance) chartInstance.destroy()

  const ctx = chartCanvas.value.getContext('2d')
  const labels = props.data.map(item => item.label || item.date)
  const values = props.data.map(item => item.count ?? item.patients ?? 0)

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Kunjungan',
        data: values,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 2,
        tension: 0.4,
        fill: true,
        pointBackgroundColor: '#3b82f6',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1e293b',
          padding: 12,
          cornerRadius: 8,
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } },
        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#64748b', font: { size: 11 }, stepSize: 1 } }
      }
    }
  })
}

onMounted(() => createChart())
watch(() => props.data, () => createChart(), { deep: true })
onBeforeUnmount(() => { if (chartInstance) chartInstance.destroy() })
</script>

<style scoped>
.chart-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #f1f5f9;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.chart-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.period-select {
  padding: 0.375rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #64748b;
  background: white;
  cursor: pointer;
  outline: none;
}

.period-select:focus {
  border-color: #3b82f6;
}

.chart-container {
  position: relative;
  height: 280px;
}
</style>
