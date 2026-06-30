# DEBUG: Super Admin Routes Redirect to Landing

## Current Issue
Clicking super admin menu items redirects to landing page instead of showing page content.

## Quick Test Steps

### 1. Check Browser Console
```
F12 → Console Tab
Look for:
- Router navigation errors
- Component loading errors
- 404 errors for views
```

### 2. Check localStorage
```javascript
// In browser console:
localStorage.getItem('token')  // Should have value
localStorage.getItem('user')   // Should have user object
JSON.parse(localStorage.getItem('user')).role  // Should be 'super_admin'
```

### 3. Manual Test URL
```javascript
// In browser console, try:
window.location.href = '/super-admin/users'
// Does it stay on the page or redirect?
```

### 4. Check Router
```javascript
// In browser console:
import.meta.env  // Check base URL
```

## Possible Causes

### A. Token/Auth Issue
**Symptom**: Redirects to `/login` or `/`
**Fix**: Re-login dengan super_admin account

```bash
# In browser:
localStorage.clear()
# Refresh page
# Login again
```

### B. Role Check Failing
**Symptom**: Redirects to `/dashboard`
**Check**: User role in localStorage

```javascript
JSON.parse(localStorage.getItem('user'))
// Should show: { ..., role: 'super_admin' }
```

### C. Router Not Loaded
**Symptom**: All routes go to landing
**Fix**: Restart dev server

```bash
cd frontend
# Kill server (Ctrl+C)
npm run dev
```

### D. Component Import Error
**Symptom**: White screen or error in console
**Check**: Browser console for import errors

## Emergency Fix: Use Flat Routes

If nested routes tidak work, revert to flat routes:

```javascript
// In router/index.js, replace nested with:
{ path: '/super-admin/users', name: 'SuperAdminUsers', component: SuperAdminUserManagementView, meta: { requiresAuth: true, portal: 'staff', roles: ['super_admin'] } },
{ path: '/super-admin/polis', name: 'SuperAdminPolis', component: SuperAdminPoliManagementView, meta: { requiresAuth: true, portal: 'staff', roles: ['super_admin'] } },
// etc...

// Then wrap each component with layout manually
```

## Commands to Run

```bash
# 1. Restart frontend
cd /data/mughni/Documents/sitaradev_hospital0/frontend
npm run dev

# 2. Check for errors during build
# Look for:
# - Import errors
# - Component registration errors
# - Route conflicts
```

## Test Checklist

- [ ] Token exists in localStorage
- [ ] User role is 'super_admin'
- [ ] No errors in browser console
- [ ] Frontend dev server running
- [ ] Can manually navigate to /super-admin/users
- [ ] Sidebar shows super admin menu items
- [ ] Clicking menu navigates (not redirects)

## If Still Failing

Try this diagnostic:

```javascript
// In browser console after login:
console.log('Token:', localStorage.getItem('token'))
console.log('User:', JSON.parse(localStorage.getItem('user')))
console.log('Current Route:', window.location.pathname)

// Try navigate:
window.location.href = '/dashboard'  // Should work
window.location.href = '/super-admin/users'  // Does it work?
```

---

**Next Step**: Run commands above and report which test fails
