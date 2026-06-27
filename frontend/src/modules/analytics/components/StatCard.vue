<template>
  <div class="stat-card">
    <div class="stat-header">
      <div class="stat-icon" :style="{ background: iconBg, color: iconColor }">
        <slot name="icon">
          <span class="icon-text">{{ icon }}</span>
        </slot>
      </div>
      <div class="stat-title">{{ title }}</div>
    </div>
    
    <div class="stat-body">
      <div class="stat-value">{{ formattedValue }}</div>
      <div v-if="subtitle" class="stat-subtitle">{{ subtitle }}</div>
    </div>
    
    <div v-if="trend !== null" class="stat-footer">
      <span :class="['trend-indicator', trend >= 0 ? 'trend-up' : 'trend-down']">
        <span class="trend-arrow">{{ trend >= 0 ? '↑' : '↓' }}</span>
        <span>{{ Math.abs(trend) }}%</span>
      </span>
      <span class="trend-label">{{ trendLabel }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  value: { type: [Number, String], required: true },
  icon: { type: String, default: '📊' },
  iconBg: { type: String, default: '#e0f2fe' },
  iconColor: { type: String, default: '#0ea5e9' },
  subtitle: { type: String, default: '' },
  trend: { type: Number, default: null },
  trendLabel: { type: String, default: '' }
})

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString('id-ID')
  }
  return props.value
})
</script>

<style scoped>
.stat-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
  border: 1px solid #f1f5f9;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.stat-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-text {
  font-size: 1.25rem;
}

.stat-title {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.stat-body {
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.875rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.2;
}

.stat-subtitle {
  font-size: 0.75rem;
  color: #94a3b8;
  margin-top: 0.25rem;
}

.stat-footer {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #f1f5f9;
}

.trend-indicator {
  display: flex;
  align-items: center;
  gap: 0.125rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.trend-up {
  color: #10b981;
}

.trend-down {
  color: #ef4444;
}

.trend-label {
  font-size: 0.75rem;
  color: #94a3b8;
}
</style>
