<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="show" class="modal-overlay" @click.self="$emit('cancel')">
        <Transition name="modal-pop">
          <div v-if="show" class="modal-card">
            <!-- Icon -->
            <div class="modal-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
            </div>

            <!-- Text -->
            <h3 class="modal-title">Keluar dari SITARA?</h3>
            <p class="modal-desc">
              Sesi Anda akan diakhiri. Pastikan semua pekerjaan sudah tersimpan sebelum keluar.
            </p>

            <!-- User info -->
            <div v-if="userName" class="user-badge">
              <div class="user-avatar">{{ userName.charAt(0).toUpperCase() }}</div>
              <div class="user-info">
                <span class="user-name">{{ userName }}</span>
                <span v-if="userRole" class="user-role">{{ userRole }}</span>
              </div>
            </div>

            <!-- Buttons -->
            <div class="modal-actions">
              <button class="btn-cancel" @click="$emit('cancel')" :disabled="loading">
                Batal
              </button>
              <button class="btn-confirm" @click="$emit('confirm')" :disabled="loading">
                <span v-if="loading" class="spinner"></span>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                  <polyline points="16 17 21 12 16 7"/>
                  <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Ya, Keluar</span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  show:     { type: Boolean, default: false },
  loading:  { type: Boolean, default: false },
  userName: { type: String,  default: '' },
  userRole: { type: String,  default: '' },
})
defineEmits(['confirm', 'cancel'])
</script>

<style scoped>
/* Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

/* Card */
.modal-card {
  background: white;
  border-radius: 1.25rem;
  padding: 2rem;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1rem;
}

/* Icon */
.modal-icon {
  width: 64px;
  height: 64px;
  background: #fef2f2;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ef4444;
}
.modal-icon svg { width: 30px; height: 30px; }

/* Text */
.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.modal-desc {
  font-size: 0.9rem;
  color: #64748b;
  line-height: 1.6;
  margin: 0;
}

/* User Badge */
.user-badge {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  padding: 0.625rem 1rem;
  width: 100%;
}
.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #1e40af);
  color: white;
  font-weight: 700;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.user-info {
  display: flex;
  flex-direction: column;
  text-align: left;
}
.user-name { font-weight: 600; font-size: 0.875rem; color: #1e293b; }
.user-role { font-size: 0.75rem; color: #64748b; }

/* Buttons */
.modal-actions {
  display: flex;
  gap: 0.75rem;
  width: 100%;
  margin-top: 0.5rem;
}

.btn-cancel {
  flex: 1;
  padding: 0.75rem;
  background: #f1f5f9;
  color: #475569;
  border: none;
  border-radius: 0.625rem;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-cancel:hover:not(:disabled) { background: #e2e8f0; }

.btn-confirm {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.75rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 0.625rem;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-confirm:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}
.btn-confirm:disabled, .btn-cancel:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-confirm svg { width: 16px; height: 16px; }

/* Spinner */
.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.65s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Animations */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.25s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.modal-pop-enter-active  { animation: popIn  0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.modal-pop-leave-active  { animation: popOut 0.2s ease-in forwards; }
@keyframes popIn  { from { opacity: 0; transform: scale(0.88) translateY(16px); } to { opacity: 1; transform: scale(1) translateY(0); } }
@keyframes popOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.92); } }
</style>
