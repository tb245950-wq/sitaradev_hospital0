<template>
  <div class="notification-container">
    <transition-group name="notification-fade">
      <div 
        v-for="notification in store.notifications" 
        :key="notification.id"
        :class="['notification', notification.type]"
      >
        {{ notification.message }}
        <button @click="store.removeNotification(notification.id)" class="close-btn">&times;</button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useNotificationStore } from '../stores/notificationStore'
const store = useNotificationStore()
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.notification {
  padding: 15px 20px;
  border-radius: 8px;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-width: 250px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.info { background-color: #3b82f6; }
.success { background-color: #10b981; }
.error { background-color: #ef4444; }
.close-btn { background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; margin-left: 10px; }
.notification-fade-enter-active,
.notification-fade-leave-active { transition: all 0.3s; }
.notification-fade-enter-from,
.notification-fade-leave-to { opacity: 0; transform: translateX(20px); }
</style>
