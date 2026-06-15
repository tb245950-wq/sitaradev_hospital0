import { useRouter, useRoute } from 'vue-router'
import { computed } from 'vue'

export function useNavigation() {
  const router = useRouter()
  const route = useRoute()

  // Navigate to dashboard
  const goToDashboard = () => {
    router.push('/dashboard')
  }

  // Navigate back to module list
  const goToModuleList = () => {
    // Extract base path (e.g., /patients/1/detail -> /patients)
    const segments = route.path.split('/')
    if (segments.length > 1) {
      router.push('/' + segments[1])
    } else {
      router.push('/dashboard')
    }
  }

  // Smart back button action
  const goBack = () => {
    // If on detail or form page, go back to list
    if (
      route.path.includes('/create') || 
      route.path.includes('/edit') || 
      route.params.id
    ) {
      goToModuleList()
    } else {
      // If on list page, go to dashboard
      goToDashboard()
    }
  }

  // Navigate using browser history
  const goBackHistory = () => {
    if (window.history.length > 1) {
      router.back()
    } else {
      goToModuleList()
    }
  }

  // Get back button text
  const backButtonText = computed(() => {
    if (route.path.includes('/create') || route.path.includes('/edit')) {
      return 'Batal'
    }
    if (route.params.id) {
      const moduleName = route.path.split('/')[1]
      const capitalizedModule = moduleName.charAt(0).toUpperCase() + moduleName.slice(1)
      return `Kembali ke Daftar ${capitalizedModule}`
    }
    return 'Kembali ke Dashboard'
  })

  // Cancel form action with confirmation
  const cancelForm = () => {
    if (confirm('Yakin ingin membatalkan? Perubahan yang belum disimpan akan hilang.')) {
      goToModuleList()
    }
  }

  return {
    goToDashboard,
    goToModuleList,
    goBack,
    cancelForm,
    backButtonText
  }
}
