# Faculty Course Allocation System
**DBMS Group Project — BITS Pilani, Goa Campus | 2025–26**

A web-based system to manage faculty, courses, departments, semesters,
and teaching allocations with role-based access control.

## Tech Stack
- Frontend: HTML5, CSS3, Vanilla JavaScript
- Backend: PHP 8 on Apache (XAMPP)
- Database: MySQL 8

## How to Run

1. Install [XAMPP](https://www.apachefriends.org/index.html) and start **Apache** and **MySQL**
2. Copy all project files into `C:\xampp\htdocs\faculty\`
3. Open `http://localhost/phpmyadmin`, create a database called `faculty_allocation_db`
4. Import `faculty_allocation_system.sql` into that database
5. Open `http://localhost/faculty/login.php`

## Login Credentials

| Role   | Email                          | Password  |
|--------|--------------------------------|-----------|
| Admin  | admin@goa.bits-pilani.ac.in   | admin123  |
| Editor | editor@goa.bits-pilani.ac.in  | edit456   |
| Viewer | viewer@goa.bits-pilani.ac.in  | view789   |

## Modules
1. Faculty Management
2. Course Management
3. Department & Semester Management
4. Allocation Management & Smart Suggestion
5. Workload Summary
6. Triggers & Transaction Management
7. Views & DBA Administration
