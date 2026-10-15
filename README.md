# Campus Emergency & Incident Management System
PHP 8.1+ (frontend + server logic) · MongoDB (database) · Bootstrap 5 · Chart.js

## Run it (Windows / Linux / macOS)
1. **Install MongoDB Community Server** and start it (`mongod`, default port 27017). Optional: MongoDB Compass to view data.
2. **Install PHP 8.1+** and **Composer**.
3. **Install the PHP MongoDB extension** (`ext-mongodb`):
   - Windows: download `php_mongodb.dll` matching your PHP version from https://pecl.php.net/package/mongodb, put in `php/ext`, add `extension=mongodb` to `php.ini`.
   - Ubuntu: `sudo apt install php-mongodb`   · macOS: `pecl install mongodb`
   - Verify: `php -m | grep mongodb`
4. In this folder run: `composer install`
5. Create sample data: `php seed.php` (use `php seed.php --reset` to wipe old users/incidents first)
6. Start server: `php -S localhost:8000`
7. Open http://localhost:8000

Custom DB address: set env var `MONGO_URI` (e.g. MongoDB Atlas string). Database name: `campus_emergency`.

## Seeded logins
Password is the role name + `123` for everyone (admin123, security123, student123, staff123).

| Login section | Name | Email |
|---|---|---|
| Admin | Admin Suresh | admin.suresh@campus.edu |
| Security Officer | Security Bala | security.bala@campus.edu |
| Security Officer | Security Murugan | security.murugan@campus.edu |
| Security Officer | Security Selvam | security.selvam@campus.edu |
| Security Officer | Security Anand | security.anand@campus.edu |
| Student | Student Renu | student.renu@campus.edu |
| Student | Student Divya | student.divya@campus.edu |
| Student | Student Arun | student.arun@campus.edu |
| Student | Student Kavin | student.kavin@campus.edu |
| Staff | Staff Lakshmi | staff.lakshmi@campus.edu |
| Staff | Staff Ramesh | staff.ramesh@campus.edu |
| Staff | Staff Priya | staff.priya@campus.edu |
| Staff | Staff Vignesh | staff.vignesh@campus.edu |

## Login
The login page has four sections: **Student, Staff, Security Officer, Admin**. The selected section must match the account's role, otherwise login is refused. Only Student and Staff can self-register; Security Officer and Admin accounts are created by the admin in *Users*.

## Roles and workflow
- **Student / Staff**: register, report incidents, view/edit own (while "Reported"), comment, follow progress.
- **Security Officer**: sees all incidents, can also report. **Allocates** each incident to a person by name (the person does not need a login), then **updates progress** (status + note). Every allocation and update is saved in the incident's progress log.
- **Admin**: does not report. Creates users and manages roles/deletion, and has a read-only view of every incident: who it is allocated to, who allocated it, current progress and full log, plus a workload table on the dashboard.

Workflow: Reported -> (allocated) In Progress -> Resolved -> Closed. An incident must be allocated before it can move past "Reported"; allocating a "Reported" incident moves it to "In Progress" automatically.

## Structure
`config.php` DB + helpers · `login/register/logout.php` auth · `dashboard.php` charts + workload · `incidents.php` list/search/filter · `incident_form.php` Create/Update · `incident_view.php` Read, allocate, progress, comments, delete · `users.php` admin user management · `seed.php` · `docs/REPORT.md`

## Troubleshooting
- *Class MongoDB\Client not found*: run `composer install`.
- *Extension missing*: step 3. *Connection refused*: MongoDB service not running.
- Old demo data still showing: run `php seed.php --reset`.
