# StaffTime - Digital Staff Timebook for Schools

A multi-school staff attendance and timebook system designed for African schools.

## Features (Version 1)
- School registration (multi-tenant)
- Admin adds and manages staff
- Staff Check-In / Check-Out
- Late / Present / Absent / Leave tracking
- Academic Session + 3 Terms
- PDF Term & Session reports
- Leave management

## Tech Stack
- PHP (simple & easy to host on Hostinger, cPanel, etc.)
- MySQL / MariaDB
- Bootstrap 5 (mobile-first)
- HTML / CSS / JavaScript

## Setup Steps

1. Create a database named `stafftime`
2. Import the file: `sql/stafftime_database.sql`
3. Edit `config/database.php` with your database details
4. Upload the whole folder to your hosting (public_html or subdomain)
5. Visit the site

## Project Structure
stafftime/ ├── admin/              # Admin portal pages ├── assets/             # CSS, JS, images ├── config/             # Database config ├── includes/           # Header, footer, functions ├── public/             # Public pages (login, register) ├── sql/                # Database schema └── README.md
## Current Status
- Database schema ready
- Project structure created
- Admin portal starting point ready

## Next Steps
1. Admin Login
2. Admin Dashboard
3. Add Staff
4. View Staff
5. Attendance logic
6. PDF generation
