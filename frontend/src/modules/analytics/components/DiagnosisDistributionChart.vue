<template>
  <div class="chart-card">
    <h3 class="chart-title">Distribusi Diagnosis</h3>
    
    <div v-if="data.length === 0" class="empty-state">
      Belum ada data diagnosis
    </div>
    
    <div v-else class="distribution-list">
      <div v-for="item in data" :key="item.category" class="distribution-item">
        <div class="distribution-info">
          <span class="distribution-label" :title="item.category">{{ item.category }}</span>
          <span class="distribution-count">{{ item.count }}</span>
        </div>
        <div class="distribution-bar-container">
          <div 
            class="distribution-bar" 
            :style="{ 
              width: `${getPercentage(item.count)}%`,
              backgroundColor: item.color 
            }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: { type: Array, required: true }
})

const maxCount = computed(() => {
  if (props.data.length === 0) return 0
  return Math.max(...props.data.map(item => item.count))
})

const getPercentage = (count) => {
  if (maxCount.value === 0) return 0
  return (count / maxCount.value) * 100
}
</script>

<style scoped>
.chart-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #f1f5f9;
}

.chart-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1.5rem 0;
}

.distribution-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.distribution-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.distribution-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.distribution-label {
  font-size: 0.8125rem;
  color: #475569;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 80%;
}

.distribution-count {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #1e293b;
}

.distribution-bar-container {
  height: 8px;
  background: #f1f5f9;
  border-radius: 4px;
  overflow: hidden;
}

.distribution-bar {
  height: 100%;
  border-radius: 4px;
  transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #94a3b8;
  font-size: 0.875rem;
  font-style: italic;
}
</style>
