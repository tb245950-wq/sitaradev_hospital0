<template>
  <div class="alert-container">
    <transition-group name="alert-slide">
      <div 
        v-for="alert in store.alerts" 
        :key="alert.id"
        :class="['alert', `alert-${alert.type}`, { 'has-action': alert.action }]"
        role="alert"
      >
        <div class="alert-content">
          <div class="alert-icon">
            <span v-if="alert.type === 'success'" class="icon">✓</span>
            <span v-else-if="alert.type === 'error'" class="icon">✕</span>
            <span v-else-if="alert.type === 'warning'" class="icon">⚠</span>
            <span v-else class="icon">ℹ</span>
          </div>
          <div class="alert-message">
            <div class="alert-title" v-if="alert.title">{{ alert.title }}</div>
            <div class="alert-text">{{ alert.message }}</div>
          </div>
        </div>
        
        <div class="alert-actions">
          <button 
            v-if="alert.action" 
            @click="handleAction(alert)"
            class="alert-btn alert-btn-primary"
          >
            {{ alert.action.label }}
          </button>
          <button @click="store.removeAlert(alert.id)" class="alert-close">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M13.5 4.5L4.5 13.5M4.5 4.5L13.5 13.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>

        <div class="alert-progress" v-if="alert.duration && alert.duration > 0">
          <div class="progress-bar" :style="{ animationDuration: `${alert.duration}ms` }"></div>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useNotificationStore } from '../stores/notificationStore'

const store = useNotificationStore()

const handleAction = (alert) => {
  if (alert.action?.callback) {
    alert.action.callback()
  }
  store.removeAlert(alert.id)
}
</script>

<style scoped>
.alert-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 450px;
}

.alert {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  border-radius: 10px;
  background: white;
  border-left: 4px solid;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  backdrop-filter: blur(10px);
  animation: slideIn 0.3s ease-out;
  position: relative;
  overflow: hidden;
}

.alert-success {
  border-left-color: #10b981;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0) 100%);
}

.alert-success .alert-icon { color: #10b981; }

.alert-error {
  border-left-color: #ef4444;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0) 100%);
}

.alert-error .alert-icon { color: #ef4444; }

.alert-warning {
  border-left-color: #f59e0b;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 158, 11, 0) 100%);
}

.alert-warning .alert-icon { color: #f59e0b; }

.alert-info {
  border-left-color: #3b82f6;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0) 100%);
}

.alert-info .alert-icon { color: #3b82f6; }

.alert-content {
  display: flex;
  gap: 12px;
  flex: 1;
  align-items: flex-start;
}

.alert-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: currentColor;
  color: white;
  flex-shrink: 0;
  font-weight: bold;
  font-size: 16px;
}

.alert-message {
  flex: 1;
}

.alert-title {
  font-weight: 600;
  font-size: 14px;
  color: #1e293b;
  margin-bottom: 4px;
}

.alert-text {
  font-size: 14px;
  color: #64748b;
  line-height: 1.4;
}

.alert-actions {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-shrink: 0;
}

.alert-btn-primary {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s;
}

.alert-btn-primary:hover {
  background: rgba(59, 130, 246, 0.1);
}

.alert-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.alert-close:hover {
  color: #64748b;
  background: rgba(0, 0, 0, 0.05);
}

.alert-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: currentColor;
  animation: progress linear forwards;
  opacity: 0.5;
}

.alert-success .progress-bar { background: #10b981; }
.alert-error .progress-bar { background: #ef4444; }
.alert-warning .progress-bar { background: #f59e0b; }
.alert-info .progress-bar { background: #3b82f6; }

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(400px) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0) translateY(0);
  }
}

@keyframes progress {
  from { width: 100%; }
  to { width: 0%; }
}

.alert-slide-enter-active,
.alert-slide-leave-active {
  transition: all 0.3s ease;
}

.alert-slide-enter-from {
  opacity: 0;
  transform: translateX(400px);
}

.alert-slide-leave-to {
  opacity: 0;
  transform: translateX(400px);
}

@media (max-width: 640px) {
  .alert-container {
    left: 12px;
    right: 12px;
    max-width: none;
  }
  
  .alert {
    flex-direction: column;
  }
  
  .alert-actions {
    width: 100%;
    justify-content: flex-end;
  }
}
</style>
