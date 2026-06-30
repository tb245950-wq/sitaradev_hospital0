# Contoh Implementasi Alert System

Berikut adalah contoh implementasi sistem alert di berbagai scenario:

## 1. Login View dengan Alert

```vue
<template>
  <div class="login-page">
    <form @submit.prevent="handleLogin">
      <input v-model="form.email" type="email" placeholder="Email">
      <input v-model="form.password" type="password" placeholder="Password">
      <button type="submit" :disabled="loading">Masuk</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const router = useRouter()
const { success, error } = useAlert()

const form = ref({
  email: '',
  password: ''
})
const loading = ref(false)

async function handleLogin() {
  if (!form.value.email || !form.value.password) {
    error('Silakan isi email dan password', 'Validation Error')
    return
  }

  loading.value = true
  try {
    const response = await api.post('/auth/login', form.value)
    localStorage.setItem('token', response.data.token)
    success('Berhasil masuk', 'Welcome!')
    router.push('/dashboard')
  } catch (err) {
    error(
      err.response?.data?.message || 'Email atau password salah',
      'Login Gagal'
    )
  } finally {
    loading.value = false
  }
}
</script>
```

## 2. Data Table dengan Delete Action

```vue
<template>
  <div class="table-container">
    <table>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>
            <button @click="deleteUser(user.id)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error } = useAlert()
const users = ref([])

async function deleteUser(id) {
  // Alert dengan action button untuk retry
  error('Apakah anda yakin ingin menghapus?', 'Konfirmasi Delete', {
    label: 'Hapus',
    callback: async () => {
      try {
        await api.delete(`/users/${id}`)
        users.value = users.value.filter(u => u.id !== id)
        success('User berhasil dihapus', 'Success')
      } catch (err) {
        error('Gagal menghapus user', 'Delete Failed')
      }
    }
  })
}
</script>
```

## 3. Form Submit dengan Validasi

```vue
<template>
  <div class="form-container">
    <form @submit.prevent="submitForm">
      <div class="form-group">
        <label>Nama Pasien</label>
        <input v-model="form.name" type="text" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input v-model="form.email" type="email" required>
      </div>

      <div class="form-group">
        <label>Nomor Telepon</label>
        <input v-model="form.phone" type="tel" required>
      </div>

      <button type="submit" :disabled="loading">Simpan</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error, warning } = useAlert()
const form = ref({
  name: '',
  email: '',
  phone: ''
})
const loading = ref(false)

async function submitForm() {
  // Validasi
  if (!form.value.name.trim()) {
    error('Nama pasien tidak boleh kosong', 'Validation Error')
    return
  }

  if (form.value.name.length < 3) {
    warning('Nama pasien minimal 3 karakter', 'Warning')
    return
  }

  loading.value = true
  try {
    const response = await api.post('/patients', form.value)
    success('Data pasien berhasil disimpan', 'Success')
    form.value = { name: '', email: '', phone: '' }
  } catch (err) {
    const message = err.response?.data?.message || 'Gagal menyimpan data'
    error(message, 'Error')
  } finally {
    loading.value = false
  }
}
</script>
```

## 4. File Upload dengan Progress Alert

```vue
<template>
  <div class="upload-container">
    <input 
      type="file" 
      @change="handleFileUpload"
      accept=".pdf,.doc,.docx"
    >
  </div>
</template>

<script setup>
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error, info } = useAlert()

async function handleFileUpload(event) {
  const file = event.target.files?.[0]
  
  if (!file) return

  // Validasi ukuran file
  const maxSize = 5 * 1024 * 1024 // 5MB
  if (file.size > maxSize) {
    error('File terlalu besar. Maksimal 5MB', 'File Error')
    return
  }

  info('Mengunggah file...', 'Uploading')
  
  const formData = new FormData()
  formData.append('file', file)

  try {
    const response = await api.post('/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    success('File berhasil diupload', 'Upload Success')
  } catch (err) {
    error('Gagal mengupload file', 'Upload Error', {
      label: 'Coba Lagi',
      callback: () => handleFileUpload(event)
    })
  }
}
</script>
```

## 5. Network Error dengan Reconnect

```vue
<script setup>
import { onMounted, ref } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error } = useAlert()
const data = ref(null)
const isLoading = ref(false)

function loadData() {
  isLoading.value = true
  api.get('/data')
    .then(res => {
      data.value = res.data
      success('Data berhasil dimuat', 'Success')
    })
    .catch(err => {
      if (!navigator.onLine) {
        error('Koneksi internet hilang', 'Network Error', {
          label: 'Retry',
          callback: loadData
        })
      } else {
        error('Gagal memuat data. Cek koneksi anda', 'Load Error', {
          label: 'Coba Lagi',
          callback: loadData
        })
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}

onMounted(() => {
  loadData()
})
</script>
```

## 6. Multi-Step Form dengan Alerts

```vue
<template>
  <div class="wizard-form">
    <div v-if="currentStep === 1">
      <!-- Step 1 Content -->
      <button @click="nextStep">Lanjut</button>
    </div>
    <div v-if="currentStep === 2">
      <!-- Step 2 Content -->
      <button @click="submit">Selesai</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error } = useAlert()
const currentStep = ref(1)
const formData = ref({})

function nextStep() {
  if (validateCurrentStep()) {
    currentStep.value++
    success('Lanjut ke tahap berikutnya', 'Step Success')
  }
}

function validateCurrentStep() {
  if (currentStep.value === 1 && !formData.value.name) {
    error('Silakan isi nama', 'Validation Error')
    return false
  }
  return true
}

async function submit() {
  try {
    await api.post('/submit', formData.value)
    success('Form berhasil disubmit', 'Submit Success')
  } catch (err) {
    error('Gagal submit form', 'Submit Error')
  }
}
</script>
```

## Tips Implementasi

1. **Selalu tangkap error API response**
   ```javascript
   try {
     await api.call()
   } catch (err) {
     const message = err.response?.data?.message || 'Terjadi kesalahan'
     error(message, 'Error')
   }
   ```

2. **Gunakan title untuk konteks yang jelas**
   ```javascript
   // Baik ✓
   success('Data berhasil diupdate', 'Update Success')
   
   // Kurang informatif ✗
   success('Berhasil')
   ```

3. **Provide action untuk error yang dapat di-retry**
   ```javascript
   error('Koneksi terputus', 'Network Error', {
     label: 'Reconnect',
     callback: reconnect
   })
   ```

4. **Disable button saat loading**
   ```vue
   <button :disabled="loading">
     {{ loading ? 'Processing...' : 'Submit' }}
   </button>
   ```

5. **Auto-dismiss durasi sesuai tipe**
   - Success: 3s (user senang, info singkat)
   - Error: 5s (user perlu baca dan mungkin ambil action)
   - Warning: 4s (informasi penting)
   - Info: 3s (notifikasi biasa)
