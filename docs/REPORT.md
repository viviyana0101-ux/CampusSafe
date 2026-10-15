# Project Report – Campus Emergency & Incident Management System
## 1. Abstract
A web portal where students and staff report emergencies/incidents, security officers allocate each incident to a responder and update its progress, and the admin manages users and monitors everything, with role-based access and analytics.
## 2. Problem Statement
Campus incidents (fire, medical, theft, harassment) are reported by phone/paper, causing delays, no tracking and no records.
## 3. Objectives
Fast digital reporting; severity and status workflow; role-based access; searchable history; dashboard analytics.
## 4. Technology Stack
PHP 8 (server-side + HTML), Bootstrap 5 (responsive UI), Chart.js (charts), MongoDB (NoSQL), Composer + mongodb/mongodb driver.
**Why MongoDB?** Flexible documents: comments are embedded in the incident; no joins; easy schema growth; fast aggregation for dashboards.
## 5. Modules
Authentication (4 login sections: Student, Staff, Security Officer, Admin, validated against the account role) · Incident reporting (student, staff, security) · Allocation of incidents to a named responder (security) · Progress updates with notes and progress log (security) · Read-only monitoring (admin) · Incident list/search/filter · Edit · Delete (admin) · Comments · Dashboard with workload · User management (admin creates users and manages roles).
## 6. Database Design
**users**: `_id, name, email (unique), password (bcrypt hash), role (student|staff|security|admin), person_id (roll no / employee ID), created_at`
**incidents**: `_id, title, category, severity, location, description, status, reporter_id, reporter_name, assigned_to (name only, no login), assigned_by, assigned_at, progress[{type,status,note,by,at}], comments[{by,text,at}], created_at, updated_at`
Indexes: users.email (unique), incidents.{status,severity}, incidents.created_at.
## 7. CRUD Mapping
| Operation | File | MongoDB call |
|---|---|---|
| Create | incident_form.php | insertOne |
| Read | incidents.php / incident_view.php | find / findOne / aggregate |
| Update | incident_form.php, incident_view.php (allocate / progress) | updateOne ($set, $push) |
| Delete | incident_view.php, users.php | deleteOne |
## 8. Security
bcrypt password hashing, session regeneration on login, CSRF tokens on all forms, output escaping (XSS), role checks on every page, regex input escaping (NoSQL injection safe).
## 9. Workflow
Reported → (security officer allocates) In Progress → Resolved → Closed. Each allocation and status change is logged with the officer's name, note and time.
## 10. Testing (sample)
| Case | Expected | Result |
|---|---|---|
| Register duplicate email | Error shown | Pass |
| Student opens another's incident | 403 | Pass |
| Security changes status | Updated | Pass |
| Non-admin opens users.php | 403 | Pass |
| Account logs in under a wrong section (e.g. Student account via Staff) | Error shown, no session | Pass |
| Student / Staff / Security reports an incident | Incident created | Pass |
| Admin opens incident_form.php | 403 | Pass |
| Security allocates incident to a name | assigned_to saved, status In Progress, log entry added | Pass |
| Status moved past Reported without allocation | Blocked with message | Pass |
| Admin opens incident | Sees allocation, progress and log, no edit/allocate controls | Pass |
| Admin creates a security officer account | Account created, can log in under Security Officer | Pass |
## 11. Future Scope
Email/SMS alerts, GPS maps, image upload, mobile app, REST API.
## 12. Conclusion
Digitizes incident management, improving response time and accountability.
## Viva Q&A
- **Why MongoDB over MySQL?** Flexible schema, embedded comments, simple aggregation.
- **How do you prevent NoSQL injection?** Typed ObjectId parsing, whitelisted values, escaped regex, driver parameterization.
- **How are passwords stored?** `password_hash` (bcrypt).
- **What is CSRF and how handled?** Forged requests; per-session token verified on each POST.
- **Which aggregate is used?** `$match` + `$group` for dashboard counts.
