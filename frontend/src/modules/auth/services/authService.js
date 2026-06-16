export const authService = {
  async login(email, password) { /* ... */ },
  async register(userData) { /* ... */ },
  async logout() { /* ... */ },
  getStoredUser() {
  try {
    const user = localStorage.getItem('user')
    if (!user || user === 'undefined' || user === 'null') {
      return null
    }
    
    if (user.startsWith('<') || user.startsWith('{error')) {
      console.warn('Invalid user data, clearing...')
      localStorage.removeItem('user')
      return null
    }
    
    return JSON.parse(user)
  } catch (error) {
    console.error('Error parsing user:', error)
    localStorage.removeItem('user')
    return null
  }
},
  getToken() {
  try {
    const token = localStorage.getItem('token')
    if (!token || token === 'undefined' || token === 'null') {
      return null
    }
    
    if (token.startsWith('<') || token.startsWith('{')) {
      console.warn('Invalid token, clearing...')
      localStorage.removeItem('token')
      return null
    }
    
    return token
  } catch (error) {
    console.error('Error getting token:', error)
    localStorage.removeItem('token')
    return null
  }
}
}
