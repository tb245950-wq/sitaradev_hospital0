# Alert System - Panduan Penggunaan

## Fitur Utama
- ✨ Alert yang modern dan interaktif
- 🎨 4 tipe alert: success, error, warning, info
- 🎯 Support untuk action buttons dengan callback
- ⏱️ Auto-dismiss dengan durasi yang dapat dikustomisasi
- 📱 Responsive design (mobile-friendly)
- 🔄 Progress bar untuk durasi countdown

## Cara Penggunaan

### 1. Import Composable di Component

```vue
<script setup>
import { useAlert } from '@/shared/composables/useAlert'

const { success, error, warning, info } = useAlert()
</script>
```

### 2. Gunakan Alert di Template atau Script

```vue
<template>
  <div>
    <button @click="handleSuccess">Show Success</button>
    <button @click="handleError">Show Error</button>
    <button @click="handleWarning">Show Warning</button>
    <button @click="handleWithAction">Show with Action</button>
  </div>
</template>

<script setup>
import { useAlert } from '@/shared/composables/useAlert'

const { success, error, warning, info } = useAlert()

const handleSuccess = () => {
  success('Data berhasil disimpan!', 'Sukses')
}

const handleError = () => {
  error('Gagal menghubungi server', 'Terjadi Kesalahan')
}

const handleWarning = () => {
  warning('Data akan dihapus permanen', 'Peringatan')
}

const handleWithAction = () => {
  error('Gagal menyimpan data', 'Error', {
    label: 'Coba Lagi',
    callback: () => {
      console.log('User clicked retry')
      success('Berhasil disimpan setelah retry')
    }
  })
}
</script>
```

### 3. Format yang Didukung

#### Alert Sederhana (hanya message)
```javascript
success('Data berhasil disimpan')
error('Terjadi kesalahan')
warning('Perhatian!')
info('Informasi penting')
```

#### Alert dengan Title
```javascript
success('File telah diupload', 'Upload Berhasil')
error('Password tidak sesuai', 'Login Gagal')
```

#### Alert dengan Action Button
```javascript
const { error } = useAlert()

error('Koneksi terputus', 'Network Error', {
  label: 'Reconnect',
  callback: () => {
    console.log('Reconnecting...')
  }
})
```

#### Alert Custom dengan Config Lengkap
```javascript
const { alert } = useAlert()

alert({
  message: 'Custom alert message',
  type: 'info',
  title: 'Custom Title',
  duration: 5000, // 5 detik sebelum auto-dismiss
  action: {
    label: 'Open',
    callback: () => {}
  }
})
```

### 4. Helper Functions

#### showApiResponse - Untuk menampilkan response API
```javascript
const { showApiResponse } = useAlert()

try {
  const response = await api.save(data)
  showApiResponse(response, 'Berhasil')
} catch (err) {
  showApiResponse({ success: false, message: err.message })
}
```

#### showError - Alert error dengan opsi retry
```javascript
const { showError } = useAlert()

showError('Gagal load data', () => {
  console.log('Retrying...')
  loadData()
}, 'Load Error')
```

## Tipe Alert dan Durasi Default

| Tipe | Durasi | Warna | Icon |
|------|--------|-------|------|
| success | 3000ms | Hijau | ✓ |
| error | 5000ms | Merah | ✕ |
| warning | 4000ms | Oranye | ⚠ |
| info | 3000ms | Biru | ℹ |

## Mengatur Durasi Custom

```javascript
alert({
  message: 'Alert yang lama',
  type: 'info',
  duration: 10000 // 10 detik
})

alert({
  message: 'Alert yang tidak hilang otomatis',
  type: 'warning',
  duration: 0 // Tidak akan hilang sampai user close
})
```

## Contoh Real-World

### Form Submission
```vue
<script setup>
import { useAlert } from '@/shared/composables/useAlert'

const { success, error } = useAlert()

async function submitForm(data) {
  try {
    const response = await api.post('/users', data)
    success('User berhasil dibuat', 'Sukses')
    // Redirect atau refresh
  } catch (err) {
    error(err.response?.data?.message || 'Gagal membuat user')
  }
}
</script>
```

### Konfirmasi Delete dengan Retry
```vue
<script setup>
import { useAlert } from '@/shared/composables/useAlert'

const { success, showError } = useAlert()

async function deleteItem(id) {
  try {
    await api.delete(`/items/${id}`)
    success('Item berhasil dihapus')
  } catch (err) {
    showError('Gagal menghapus item', () => deleteItem(id), 'Delete Error')
  }
}
</script>
```

## Tips & Tricks

1. **Gunakan title untuk konteks yang jelas**
   ```javascript
   error('Email sudah terdaftar', 'Registrasi Gagal')
   // Lebih informatif daripada
   error('Email sudah terdaftar')
   ```

2. **Action buttons untuk user yang perlu aksi**
   ```javascript
   warning('Session anda berakhir', 'Session Expired', {
     label: 'Login Ulang',
     callback: () => router.push('/login')
   })
   ```

3. **Clear all alerts jika diperlukan**
   ```javascript
   const { clearAll } = useAlert()
   clearAll() // Menghapus semua alert
   ```

## Styling

Alert menggunakan Tailwind-compatible colors:
- Success: #10b981
- Error: #ef4444
- Warning: #f59e0b
- Info: #3b82f6

Anda dapat memodifikasi warna di `AlertNotification.vue` di file styles jika diperlukan.
