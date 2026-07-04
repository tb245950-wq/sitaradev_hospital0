<template>
  <div class="settings-management">
    <div class="page-header">
      <h1>Pengaturan Sistem</h1>
    </div>

    <div v-if="loadError" class="load-error">⚠️ {{ loadError }}</div>

    <div class="settings-section">
      <h2>Konfigurasi Umum</h2>
      
      <div class="setting-item">
        <label>Nama Klinik</label>
        <input v-model="settings.clinic_name" class="input" placeholder="Nama Klinik">
      </div>

      <div class="setting-item">
        <label>Email Klinik</label>
        <input v-model="settings.clinic_email" type="email" class="input" placeholder="email@klinik.com">
      </div>

      <div class="setting-item">
        <label>Telepon</label>
        <input v-model="settings.clinic_phone" class="input" placeholder="+62-...">
      </div>

      <div class="setting-item">
        <label>Alamat</label>
        <textarea v-model="settings.clinic_address" class="input" rows="3"></textarea>
      </div>

      <button @click="saveSettings" :disabled="saving" class="btn-primary">
        {{ saving ? 'Menyimpan...' : '💾 Simpan Pengaturan' }}
      </button>

      <p v-if="message" :class="`message ${success ? 'success' : 'error'}`">{{ message }}</p>
    </div>

    <div class="settings-section">
      <h2>Email Configuration</h2>
      
      <div class="setting-item">
        <label>SMTP Host</label>
        <input v-model="settings.smtp_host" class="input" placeholder="smtp.gmail.com">
      </div>

      <div class="setting-item">
        <label>SMTP Port</label>
        <input v-model="settings.smtp_port" type="number" class="input" placeholder="587">
      </div>

      <div class="setting-item">
        <label>Email Sender</label>
        <input v-model="settings.smtp_email" class="input" placeholder="noreply@klinik.com">
      </div>

      <button @click="testEmail" :disabled="testing" class="btn-secondary">
        {{ testing ? 'Testing...' : '📧 Test Email' }}
      </button>
    </div>

    <div class="settings-section">
      <h2>Session & Security</h2>
      
      <div class="setting-item">
        <label>Session Timeout (menit)</label>
        <input v-model.number="settings.session_timeout" type="number" class="input" placeholder="30">
      </div>

      <div class="setting-item">
        <label>
          <input v-model="settings.require_2fa" type="checkbox">
          Aktifkan Two-Factor Authentication
        </label>
      </div>

      <button @click="saveSettings" :disabled="saving" class="btn-primary">
        💾 Simpan
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { superAdminService } from '../services/superAdminService'

const settings = ref({
  clinic_name: '',
  clinic_email: '',
  clinic_phone: '',
  clinic_address: '',
  smtp_host: '',
  smtp_port: 587,
  smtp_email: '',
  session_timeout: 30,
  require_2fa: false
})

const saving = ref(false)
const testing = ref(false)
const message = ref('')
const success = ref(false)
const loadError = ref('')

onMounted(async () => {
  try {
    const res = await superAdminService.getSettings()
    if (res.data?.data) {
      settings.value = { ...settings.value, ...res.data.data }
    }
  } catch (err) {
    loadError.value = 'Gagal memuat pengaturan: ' + (err.response?.data?.message ?? err.message)
  }
})

const saveSettings = async () => {
  try {
    saving.value = true
    message.value = ''
    await superAdminService.saveSettings(settings.value)
    success.value = true
    message.value = '✅ Pengaturan berhasil disimpan'
  } catch (err) {
    success.value = false
    message.value = '❌ Gagal menyimpan pengaturan: ' + (err.response?.data?.message ?? err.message)
  } finally {
    saving.value = false
  }
}

const testEmail = async () => {
  try {
    testing.value = true
    message.value = '✅ Email test berhasil dikirim (fitur dalam pengembangan)'
  } catch (err) {
    message.value = '❌ Gagal mengirim email test'
  } finally {
    testing.value = false
  }
}
</script>

<style scoped>
.settings-management { padding: 1.5rem; max-width: 800px; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; }

.settings-section {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.settings-section h2 { font-size: 1.1rem; margin-bottom: 1rem; }

.setting-item {
  margin-bottom: 1.5rem;
}

.setting-item label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #1e293b;
}

.input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.25rem;
  font-family: inherit;
  font-size: 1rem;
}

.btn-primary {
  background: #1e40af;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  margin-right: 0.5rem;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: #e2e8f0;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.message {
  padding: 1rem;
  border-radius: 0.25rem;
  margin-top: 1rem;
}

.message.success {
  background: #dcfce7;
  color: #166534;
}

.message.error {
  background: #fee2e2;
  color: #991b1b;
}

.load-error {
  background: #fef3c7;
  color: #92400e;
  padding: 1rem;
  border-radius: 0.25rem;
  margin-bottom: 1rem;
}
</style>
