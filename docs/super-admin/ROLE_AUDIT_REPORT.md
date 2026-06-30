# 🔍 ROLE AUDIT REPORT - Semua Role & Permissions

## Summary: 6 Role Total

```
1. super_admin      ← NEW (System Administration)
2. admin            ← EXISTING (Clinic Operations / Admin Klinik)
3. dokter           ← EXISTING (Doctor)
4. terapis          ← EXISTING (Therapist)
5. resepsionis      ← EXISTING (Receptionist - mentioned but not fully implemented)
6. pasien           ← EXISTING (Patient - patient portal)
```

---

## DETAILED ROLE BREAKDOWN

### 1. SUPER_ADMIN (NEW - SYSTEM ADMINISTRATION)
**Backend Files**:
- SuperAdminController.php (24 references)
- AnalyticsController.php (reject super_admin)
- api.php routes (super-admin group)

**Access**:
✅ /super-admin/dashboard
✅ /super-admin/audit-logs
✅ /super-admin/login-history
✅ /super-admin/failed-logins
✅ /super-admin/users (CRUD + password reset)
✅ /super-admin/polis (view-only)

❌ /patients/* (403)
❌ /queues/* (403)
❌ /assessments/* (403)
❌ /therapies/* (403)
❌ /monitoring/* (403)

**Frontend**:
- DashboardView.vue: renders SuperAdminDashboard
- SuperAdminDashboard.vue: system admin features

---

### 2. ADMIN (EXISTING - CLINIC OPERATIONS / ADMIN KLINIK)
**Backend Files**:
- UserManagementController.php (12 references)
- AssessmentController.php (7 references)
- TherapyController.php (3 references)
- QueueController.php (3 references)
- PatientController.php (2 references)
- ReportController.php (4 references)
- MonitoringController.php (3 references)
- api.php routes (admin, patients, queues, assessments, therapies, monitoring)

**Validation Rules**:
- SuperAdminController: 'role' => 'required|in:admin,dokter,terapis,resepsionis'
- UserManagementController: 'role' => 'required|in:admin,dokter,terapis'
- AuthController: 'role' => 'required|in:dokter,terapis'

**Access**:
✅ /patients/* (CRUD)
✅ /queues/* (CRUD)
✅ /assessments/* (CRUD)
✅ /therapies/* (CRUD)
✅ /monitoring/* (CRUD)
✅ /polis/* (CRUD)
✅ /analytics/dashboard
✅ /admin/users

❌ /super-admin/* (403)

**Frontend**:
- DashboardView.vue: renders AdminDashboard
- AdminDashboard.vue: operational features
- Sidebar: shows patient, queue, assessment, therapy menus

---

### 3. DOKTER (EXISTING - DOCTOR)
**Backend Files**:
- AssessmentController.php (7 references)
- AnalyticsController.php (dokterData method)
- TherapyController.php (3 references)
- PatientAuthController.php (getDoctors endpoint)
- ReportController.php (4 references)
- MonitoringController.php (3 references)

**Access**:
✅ /patients/* (read + update)
✅ /queues/* (CRUD)
✅ /assessments/* (CRUD)
✅ /therapies/* (CRUD)
✅ /monitoring/* (CRUD)
✅ /polis (read-only)
✅ /analytics/dashboard (dokter view)

❌ /super-admin/* (403)
❌ /assessments/* destroy (only admin)

**Frontend**:
- DashboardView.vue: renders DoctorDashboard
- Sidebar: shows patient, queue, assessment, therapy menus

---

### 4. TERAPIS (EXISTING - THERAPIST)
**Backend Files**:
- AnalyticsController.php (terapisData method)
- MonitoringController.php (3 references)
- TherapyController.php (3 references)
- PatientAuthController.php (getDoctors include terapis)

**Access**:
✅ /patients/* (read-only)
✅ /queues/* (CRUD)
✅ /therapies/* (CRUD)
✅ /monitoring/* (CRUD)
✅ /polis (read-only)
✅ /analytics/dashboard (terapis view)

❌ /assessments/* (403)
❌ /super-admin/* (403)

**Frontend**:
- DashboardView.vue: renders TerapisDashboard
- Sidebar: shows queue, therapy, monitoring menus

---

### 5. RESEPSIONIS (EXISTING - MENTIONED BUT NOT FULLY IMPLEMENTED)
**Status**: Mentioned in SuperAdminController validation but no specific endpoint/controller

**Backend References**:
- SuperAdminController.php: 'role' => 'in:admin,dokter,terapis,resepsionis'

**Frontend**: Not explicitly handled in DashboardView.vue (would show "Dashboard belum tersedia")

**TODO**: Implement receptionist dashboard & features

---

### 6. PASIEN (EXISTING - PATIENT PORTAL)
**Backend Files**:
- PatientAuthController.php (12 references)
- AnalyticsController.php (reject pasien)
- Multiple controllers check: role !== 'pasien'

**Access**:
✅ /pasien/dashboard
✅ /pasien/profile (get + update)
✅ /pasien/booking
✅ /pasien/doctors
✅ /pasien/polis
✅ /pasien/antrian-saya
✅ /pasien/jadwal-terapi
✅ /pasien/riwayat-medis

❌ All staff endpoints

**Frontend**:
- DashboardView.vue: redirect to /pasien/dashboard
- PatientDashboardView.vue: patient portal
- PatientBookingView.vue: booking system

---

## ROLE MATRIX

```
                          super_admin  admin  dokter  terapis  resepsionis  pasien
System Admin                   ✅        ❌      ❌       ❌         ❌         ❌
User Management                ✅        ❌      ❌       ❌         ❌         ❌
Audit Logs                      ✅        ❌      ❌       ❌         ❌         ❌
Patient Data                    ❌        ✅      ✅       ✅         ?          ✅(own)
Queue Management                ❌        ✅      ✅       ✅         ?          ❌
Assessment                      ❌        ✅      ✅       ❌         ❌         ❌
Therapy                         ❌        ✅      ✅       ✅         ❌         ❌
Monitoring                      ❌        ✅      ✅       ✅         ❌         ❌
Analytics Dashboard             ❌        ✅      ✅       ✅         ?          ❌
Patient Portal                  ❌        ❌      ❌       ❌         ❌         ✅
```

---

## INCONSISTENCIES & ISSUES

### 🔴 ISSUE 1: Resepsionis Role Mentioned But Not Implemented
- Validation allows: `in:admin,dokter,terapis,resepsionis`
- But no specific features/endpoints for resepsionis
- No dashboard in frontend
- **ACTION**: Define resepsionis role clearly or remove from validation

### 🟡 ISSUE 2: Multiple Role Validation Formats
- SuperAdminController: 'role' => 'in:admin,dokter,terapis,resepsionis'
- UserManagementController: 'role' => 'in:admin,dokter,terapis' (missing resepsionis)
- AuthController: 'role' => 'in:dokter,terapis' (register endpoint, limited)
- **ACTION**: Standardize role validation

### 🟡 ISSUE 3: Sidebar Doesn't Show Super Admin Menu
- Sidebar.vue doesn't have super_admin conditional
- Super admin won't see navigation menu options
- **ACTION**: Add super_admin menu to Sidebar

### 🟡 ISSUE 4: AnalyticsController Default Case
- Returns null for roles not in admin/dokter/terapis match
- Super admin correctly rejected but response could be clearer
- **ACTION**: Already fixed with explicit super_admin check

---

## RECOMMENDATIONS

### ✅ DO THIS:

1. **Clarify Resepsionis Role**
   - Either implement full receptionist features
   - Or remove from validation and use only: admin, dokter, terapis

2. **Update Sidebar.vue**
   - Add super_admin conditional for navigation menu
   - Show admin menu for admin role

3. **Standardize Role Validation**
   - Use consistent role lists across all validators
   - Recommended: `admin,dokter,terapis,resepsionis` or just `admin,dokter,terapis`

4. **Document Role Responsibilities**
   - Create ROLE_DEFINITIONS.md with clear descriptions
   - Keep this audit report updated

5. **Implement Resepsionis Dashboard** (if needed)
   - Or exclude from UI entirely

---

## FILES NEEDING UPDATE

```
BACKEND:
❌ UserManagementController.php - Add resepsionis to validation
❌ Standardize role lists across all controllers

FRONTEND:
❌ Sidebar.vue - Add super_admin menu conditional
❌ Sidebar.vue - Add admin menu conditional (if missing)

DATABASE:
✅ Already supports any role string value
```

---

**Audit Date**: 29 Juni 2026, 19:15 WIB  
**Total Roles Found**: 6  
**Issues Found**: 4  
**Critical Issues**: 0  
**Recommendations**: 5
