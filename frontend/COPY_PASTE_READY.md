# 📋 Copy-Paste Ready Examples

Siap copy-paste ke project Anda! Tinggal sesuaikan dengan kebutuhan.

## 1️⃣ Simple Form Submit

```vue
<template>
  <div class="form-container">
    <input v-model="form.name" type="text" placeholder="Nama">
    <input v-model="form.email" type="email" placeholder="Email">
    <button @click="submit" :disabled="loading">Simpan</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'  // Sesuaikan path API Anda

const { success, error } = useAlert()
const form = ref({ name: '', email: '' })
const loading = ref(false)

async function submit() {
  loading.value = true
  try {
    await api.post('/users', form.value)
    success('User berhasil ditambahkan', 'Success')
    form.value = { name: '', email: '' }
  } catch (err) {
    error(err.response?.data?.message || 'Gagal menambahkan user')
  } finally {
    loading.value = false
  }
}
</script>
```

## 2️⃣ Delete Button with Confirmation

```vue
<template>
  <button @click="deleteItem(id)">Delete</button>
</template>

<script setup>
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error } = useAlert()

function deleteItem(id) {
  error('Apakah anda yakin ingin menghapus?', 'Konfirmasi Delete', {
    label: 'Hapus',
    callback: async () => {
      try {
        await api.delete(`/items/${id}`)
        success('Item berhasil dihapus', 'Success')
        // Refresh data atau navigate
      } catch (err) {
        error('Gagal menghapus item')
      }
    }
  })
}
</script>
```

## 3️⃣ Login Form

```vue
<template>
  <form @submit.prevent="handleLogin">
    <input v-model="email" type="email" placeholder="Email" required>
    <input v-model="password" type="password" placeholder="Password" required>
    <button type="submit" :disabled="loading">
      {{ loading ? 'Loading...' : 'Masuk' }}
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const router = useRouter()
const { success, error } = useAlert()
const email = ref('')
const password = ref('')
const loading = ref(false)

async function handleLogin() {
  loading.value = true
  try {
    const res = await api.post('/auth/login', { email: email.value, password: password.value })
    localStorage.setItem('token', res.data.token)
    success('Selamat datang!', 'Login Berhasil')
    router.push('/dashboard')
  } catch (err) {
    error(err.response?.data?.message || 'Email atau password salah', 'Login Gagal')
  } finally {
    loading.value = false
  }
}
</script>
```

## 4️⃣ API Call with Error Handling

```vue
<script setup>
import { ref, onMounted } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error, warning } = useAlert()
const data = ref(null)
const loading = ref(false)

function loadData() {
  loading.value = true
  api.get('/data')
    .then(res => {
      data.value = res.data
      if (res.data.length === 0) {
        warning('Tidak ada data ditemukan', 'Empty Result')
      } else {
        success(`${res.data.length} data berhasil dimuat`, 'Load Success')
      }
    })
    .catch(err => {
      error(
        err.response?.data?.message || 'Gagal memuat data',
        'Load Error',
        {
          label: 'Coba Lagi',
          callback: loadData
        }
      )
    })
    .finally(() => {
      loading.value = false
    })
}

onMounted(() => loadData())
</script>
```

## 5️⃣ File Upload

```vue
<template>
  <input type="file" @change="handleUpload" accept=".pdf,.doc,.docx">
</template>

<script setup>
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error, info } = useAlert()

async function handleUpload(event) {
  const file = event.target.files?.[0]
  if (!file) return

  // Validate size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    error('File terlalu besar. Max 5MB', 'File Error')
    return
  }

  info('Mengunggah file...', 'Uploading')

  const formData = new FormData()
  formData.append('file', file)

  try {
    await api.post('/upload', formData)
    success('File berhasil diupload', 'Upload Success')
    event.target.value = '' // Reset input
  } catch (err) {
    error('Gagal upload file', 'Upload Error', {
      label: 'Coba Lagi',
      callback: () => handleUpload(event)
    })
  }
}
</script>
```

## 6️⃣ Data Table with Edit/Delete

```vue
<template>
  <table>
    <tbody>
      <tr v-for="user in users" :key="user.id">
        <td>{{ user.name }}</td>
        <td>{{ user.email }}</td>
        <td>
          <button @click="editUser(user)">Edit</button>
          <button @click="deleteUser(user.id)">Delete</button>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error } = useAlert()
const users = ref([])

function deleteUser(id) {
  error('Hapus user ini?', 'Confirm Delete', {
    label: 'Hapus',
    callback: async () => {
      try {
        await api.delete(`/users/${id}`)
        users.value = users.value.filter(u => u.id !== id)
        success('User berhasil dihapus')
      } catch (err) {
        error('Gagal menghapus user')
      }
    }
  })
}

async function editUser(user) {
  // Implementasi edit...
  success('User berhasil diupdate', 'Update Success')
}

onMounted(async () => {
  try {
    const res = await api.get('/users')
    users.value = res.data
  } catch (err) {
    error('Gagal load users')
  }
})
</script>
```

## 7️⃣ Validation with Alert

```vue
<template>
  <form @submit.prevent="submit">
    <input v-model="form.name" type="text">
    <input v-model="form.email" type="email">
    <button type="submit">Simpan</button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'

const { error, success } = useAlert()
const form = ref({ name: '', email: '' })

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

function submit() {
  // Validate name
  if (!form.value.name.trim()) {
    error('Nama tidak boleh kosong', 'Validation Error')
    return
  }

  if (form.value.name.length < 3) {
    error('Nama minimal 3 karakter', 'Validation Error')
    return
  }

  // Validate email
  if (!form.value.email) {
    error('Email tidak boleh kosong', 'Validation Error')
    return
  }

  if (!validateEmail(form.value.email)) {
    error('Format email tidak valid', 'Validation Error')
    return
  }

  // All valid, submit
  success('Form valid, siap submit!', 'Validation Success')
  // TODO: actual submit
}
</script>
```

## 8️⃣ Network Error with Retry

```vue
<script setup>
import { ref } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error } = useAlert()
const data = ref(null)

async function fetchData() {
  try {
    const res = await api.get('/data')
    data.value = res.data
    success('Data berhasil dimuat', 'Success')
  } catch (err) {
    if (!navigator.onLine) {
      error(
        'Internet tidak tersambung. Periksa koneksi anda',
        'Network Error',
        { label: 'Coba Lagi', callback: fetchData }
      )
    } else if (err.response?.status === 500) {
      error(
        'Server sedang bermasalah. Coba lagi nanti',
        'Server Error',
        { label: 'Coba Lagi', callback: fetchData }
      )
    } else {
      error(
        err.response?.data?.message || 'Gagal memuat data',
        'Load Error',
        { label: 'Coba Lagi', callback: fetchData }
      )
    }
  }
}

// Trigger on component mount
onMounted(() => fetchData())
</script>
```

## 9️⃣ Multi-Step Form Wizard

```vue
<template>
  <div class="wizard">
    <!-- Step 1 -->
    <div v-if="step === 1" class="form-step">
      <h3>Step 1: Personal Info</h3>
      <input v-model="form.name" placeholder="Name">
      <button @click="nextStep">Lanjut</button>
    </div>

    <!-- Step 2 -->
    <div v-if="step === 2" class="form-step">
      <h3>Step 2: Contact Info</h3>
      <input v-model="form.email" type="email" placeholder="Email">
      <button @click="prevStep">Kembali</button>
      <button @click="nextStep">Lanjut</button>
    </div>

    <!-- Step 3 -->
    <div v-if="step === 3" class="form-step">
      <h3>Step 3: Review</h3>
      <p>Name: {{ form.name }}</p>
      <p>Email: {{ form.email }}</p>
      <button @click="prevStep">Kembali</button>
      <button @click="submit">Selesai</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAlert } from '@/shared/composables/useAlert'
import { api } from '@/services/api'

const { success, error } = useAlert()
const step = ref(1)
const form = ref({ name: '', email: '' })

function nextStep() {
  if (step.value === 1 && !form.value.name) {
    error('Nama wajib diisi', 'Validation Error')
    return
  }
  if (step.value === 2 && !form.value.email) {
    error('Email wajib diisi', 'Validation Error')
    return
  }
  step.value++
  success('Lanjut ke step berikutnya', 'Success')
}

function prevStep() {
  if (step.value > 1) step.value--
}

async function submit() {
  try {
    await api.post('/register', form.value)
    success('Registrasi berhasil!', 'Success')
    // Navigate or reset
  } catch (err) {
    error(err.response?.data?.message || 'Gagal registrasi')
  }
}
</script>
```

## 🔟 Toast Notification (Non-blocking)

```vue
<script setup>
import { useAlert } from '@/shared/composables/useAlert'

const { info, warning } = useAlert()

function showNotification() {
  // Info - short duration, user-friendly
  info('New message received', 'New Message')
  
  // Warning - medium duration, needs attention
  warning('Your session will expire in 5 minutes', 'Session Warning')
}
</script>
```

---

## 💡 Tips Menggunakan Contoh Di Atas

1. **Sesuaikan path API** - Ganti `/users` dengan path API Anda
2. **Import statement** - Pastikan path import `useAlert` benar
3. **Error handling** - Customize error message sesuai backend Anda
4. **API service** - Sesuaikan dengan API client yang Anda gunakan
5. **Styling** - Alert sudah styled, hanya form/button yang perlu di-style

## ✅ Checklist Sebelum Copy-Paste

- [ ] Import `useAlert` sudah benar
- [ ] Path API sudah sesuai
- [ ] Error handling sudah customize
- [ ] Component sudah punya base structure
- [ ] Browser console tidak ada error

---

**Siap? Pilih contoh di atas dan mulai copy-paste!** 🚀
