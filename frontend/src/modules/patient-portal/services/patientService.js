export const patientService = {
  async login(email, password) { /* ... */ },
  async register(patientData) { /* ... */ },
  async logout() { /* ... */ },
  getStoredUser() {
  try {
    const user = localStorage.getItem('patient_user')
    if (!user || user === 'undefined' || user === 'null') {
      return null
    }
    
    // Cek apakah ini JSON valid
    if (user.startsWith('<') || user.startsWith('{error')) {
      console.warn('Invalid user data in localStorage, clearing...')
      localStorage.removeItem('patient_user')
      return null
    }
    
    return JSON.parse(user)
  } catch (error) {
    console.error('Error parsing patient_user:', error)
    localStorage.removeItem('patient_user')
    return null
  }
},
  getToken() {
    try {
      const token = localStorage.getItem('patient_token')
      if (token && typeof token === 'string' && !token.startsWith('<')) {
        return token
      }
      if (token && token.startsWith('<')) {
        console.error('Invalid token detected (HTML instead of JWT), clearing...')
        localStorage.removeItem('patient_token')
      }
      return null
    } catch (error) {
      console.error('Error getting token:', error)
      localStorage.removeItem('patient_token')
      return null
    }
  },
  isAuthenticated() {
    return !!this.getToken()
  }
}
