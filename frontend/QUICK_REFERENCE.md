# 🚀 Alert System - Quick Reference

## 1 Menit Setup

```javascript
// Import di component Anda
import { useAlert } from '@/shared/composables/useAlert'

const { success, error, warning, info } = useAlert()
```

## Paling Sering Digunakan

```javascript
// Success
success('Data berhasil disimpan!')

// Error
error('Gagal menyimpan data')

// Warning
warning('Ini akan dihapus permanen')

// Info
info('Silakan baca dengan baik')
```

## Dengan Title (Recommended)

```javascript
success('File uploaded', 'Upload Success')
error('Email already exists', 'Registration Error')
warning('Are you sure?', 'Confirm Delete')
info('New update available', 'Notice')
```

## Dengan Action Button

```javascript
error('Connection lost', 'Network Error', {
  label: 'Retry',
  callback: () => {
    console.log('User clicked retry')
    retryConnection()
  }
})
```

## Dalam Form Submission

```javascript
async function handleSubmit() {
  try {
    const res = await api.post('/data', formData)
    success('Saved!', 'Success')
  } catch (err) {
    error(err.response?.data?.message, 'Error')
  }
}
```

## Custom Duration (dalam ms)

```javascript
// Alert yang stay longer
const { alert } = useAlert()

alert({
  message: 'Important message',
  type: 'warning',
  title: 'Important',
  duration: 10000 // 10 detik
})

// Alert yang tidak hilang otomatis
alert({
  message: 'This stays until user closes it',
  type: 'info',
  duration: 0 // Never auto-dismiss
})
```

## Default Durations

- `success()` → 3 detik
- `error()` → 5 detik  
- `warning()` → 4 detik
- `info()` → 3 detik

## Alert Types & Colors

| Type | Color | Durasi |
|------|-------|--------|
| success | 🟢 Green | 3s |
| error | 🔴 Red | 5s |
| warning | 🟠 Orange | 4s |
| info | 🔵 Blue | 3s |

## Contoh Real-World

### Delete with Confirmation
```javascript
error('Delete this item?', 'Confirm', {
  label: 'Delete',
  callback: () => api.delete(`/items/${id}`)
})
```

### API Error with Retry
```javascript
async function loadData() {
  try {
    const data = await api.get('/data')
  } catch (err) {
    error('Failed to load', 'Error', {
      label: 'Retry',
      callback: loadData
    })
  }
}
```

### Form Validation
```javascript
if (!email) {
  error('Email is required', 'Validation Error')
  return
}

success('Form submitted', 'Success')
```

## All Available Methods

```javascript
const {
  alert,           // Custom alert with full config
  success,         // Success shortcut
  error,           // Error shortcut
  warning,         // Warning shortcut
  info,            // Info shortcut
  clearAll,        // Clear all alerts
  showApiResponse, // Helper for API responses
  showError        // Error with retry helper
} = useAlert()
```

## Helper Functions

### showApiResponse
```javascript
// For API responses
const res = await api.save(data)
showApiResponse(res, 'Saved successfully')
```

### showError with Retry
```javascript
showError('Failed to load', () => {
  console.log('Retrying...')
  loadData()
}, 'Load Error')
```

## Tips & Tricks

✅ **Do:**
- Use title for clarity: `success('Saved', 'Success')`
- Provide retry for network errors
- Use appropriate duration
- Clear alerts on page navigation

❌ **Don't:**
- Spam alerts: max 1-2 at a time
- Use confusing messages
- Forget to handle errors
- Leave old alerts on page change

## File Locations

- **Component**: `src/shared/components/AlertNotification.vue`
- **Store**: `src/shared/stores/notificationStore.js`
- **Composable**: `src/shared/composables/useAlert.js`
- **Docs**: `ALERT_SYSTEM.md`, `EXAMPLE_USAGE.md`

## Debug Mode

Check browser console:
```javascript
// See all current alerts
import { useNotificationStore } from '@/shared/stores/notificationStore'
const store = useNotificationStore()
console.log(store.alerts)
```

---

**Need more details?** See `ALERT_SYSTEM.md` for full documentation.
