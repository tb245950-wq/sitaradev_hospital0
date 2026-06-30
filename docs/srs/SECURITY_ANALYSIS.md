# Cybersecurity Vulnerability Analysis - SITARA (CIA Triad)

## 1. Confidentiality
- **Threat**: Unauthorized access to patient medical records (PII).
- **Vulnerabilities**: 
  - Potential weak API authentication in `api.php` if Sanctum token is compromised.
  - Insecure storage of sensitive data in PostgreSQL (e.g., lack of encryption at rest).
- **Mitigation**: Ensure strict role-based access control (RBAC), enforce HTTPS (TLS), and consider encrypting PII fields in the database.

## 2. Integrity
- **Threat**: Unauthorized modification of medical assessments or patient records.
- **Vulnerabilities**: 
  - Mass assignment in `Queue::create` or `Assessment::update` if not properly filtered in `fillable`.
  - Inadequate input validation in controllers (e.g., `PatientPortalController`).
- **Mitigation**: Always use Form Requests for validation, keep `fillable` limited, and implement audit logs (already partially implemented with `ActivityLog`).

## 3. Availability
- **Threat**: Denial of Service (DoS) targeting the booking API.
- **Vulnerabilities**: 
  - Unprotected booking endpoint allowing excessive requests.
  - Lack of rate limiting on login/register endpoints.
- **Mitigation**: Implement Laravel Rate Limiting on all API routes, especially public ones like login/register/booking.

## Recommendations
- **Priority 1**: Implement comprehensive rate limiting in `app/Providers/RouteServiceProvider.php`.
- **Priority 2**: Review all controllers to ensure `Request` validation is used instead of manual array validation.
- **Priority 3**: Ensure all database foreign keys are strictly enforced and cascading rules are audited.
