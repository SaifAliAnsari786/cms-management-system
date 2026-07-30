# CMS Management System API

A production-ready Content Management System (CMS) backend developed with **Laravel 12**. This project provides secure REST APIs with authentication, role-based access control (RBAC), dynamic page management, menu management, Swagger/OpenAPI documentation, and automated feature tests.

---

## Repository

**GitHub Repository**

https://github.com/SaifAliAnsari786/cms-management-system

Clone the repository:

```bash
git clone https://github.com/SaifAliAnsari786/cms-management-system.git
```

Navigate to the backend project:

```bash
cd cms-management-system/backend
```

---

# Technology Stack

- Laravel 12
- PHP 8.2+
- Laravel Sanctum
- Spatie Laravel Permission
- MySQL / PostgreSQL / SQLite
- Swagger / OpenAPI (L5 Swagger)
- PHPUnit
- ReactJS (Frontend - Assignment Requirement)

---

# Features

## Authentication

- Laravel Sanctum Authentication
- User Login
- User Logout
- Protected API Routes

## User Management

- Create User
- Update User
- Delete User
- List Users
- Assign Roles

## Role Management

- Create Roles
- Update Roles
- Delete Roles
- Assign Permissions

## Permission Management

- Create Permissions
- Update Permissions
- Delete Permissions
- Role-Based Access Control (RBAC)

## Page Management

- Create Pages
- Update Pages
- Delete Pages
- Restore Deleted Pages
- Soft Delete Support
- CKEditor Content
- Cover Image Upload
- Draft & Published Status
- Scheduled Publishing
- Audit Fields (Created By & Updated By)

## Dynamic Menu

- Create Menu
- Update Menu
- Delete Menu
- Nested Menu Structure
- Sortable Menu Items
- Associate Pages with Menu Items

## REST API Features

- Form Request Validation
- API Resources
- Authentication
- Authorization
- Pagination
- Search
- Filtering
- Standard JSON Responses

## API Documentation

- Swagger / OpenAPI Documentation
- Interactive Swagger UI

## Automated Testing

- Authentication Tests
- Authorization Tests
- Restore Endpoint Tests
- Swagger Configuration Tests

---

# Project Structure

```
backend/
├── app/
│   ├── Console/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   ├── OpenApi/
│   ├── Providers/
│   └── helpers.php
│
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── composer.json
└── README.md
```

---

# System Requirements

- PHP 8.2 or higher
- Composer
- MySQL / PostgreSQL / SQLite
- Git

---

# Installation

## 1. Clone the Repository

```bash
git clone https://github.com/SaifAliAnsari786/cms-management-system.git
```

## 2. Move to Backend Directory

```bash
cd cms-management-system/backend
```

## 3. Install Dependencies

```bash
composer install
```

## 4. Create Environment File

```bash
cp .env.example .env
```

## 5. Generate Application Key

```bash
php artisan key:generate
```

## 6. Configure Database

Update the following values in your `.env` file.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 7. Run Database Migrations & Seeders

```bash
php artisan migrate --seed
```

## 8. Create Storage Link

```bash
php artisan storage:link
```

## 9. Start the Development Server

```bash
php artisan serve
```

Laravel will display the application URL in the terminal.

Example:

```
INFO  Server running on [http://127.0.0.1:8000]
```

If port **8000** is unavailable, Laravel will automatically use another available port.

---

# Default Login Credentials

## Administrator

| Email | Password |
|--------|----------|
| admin@example.com | password |

> Update these credentials if your database seeders use different values.

---

# Swagger Documentation

Generate the API documentation.

```bash
php artisan l5-swagger:generate
```

Open Swagger UI in your browser.

```
<APP_URL>/api/documentation
```

Example:

```
http://127.0.0.1:8000/api/documentation
```

---

# Running Automated Tests

Run all tests.

```bash
php artisan test
```

Run a specific test.

```bash
php artisan test --filter=PageRestoreTest
```

Run Swagger configuration test.

```bash
php artisan test --filter=SwaggerConfigTest
```

---

# Scheduled Publishing

Run the Laravel scheduler.

```bash
php artisan schedule:work
```

or

```bash
php artisan schedule:run
```

Pages scheduled for future publication will automatically become publicly available once their publish date has passed.

---

# Custom Helper

A reusable helper has been implemented for common application functionality.

Location:

```
app/helpers.php
```

---

# Main Packages

| Package | Purpose |
|----------|---------|
| Laravel Sanctum | API Authentication |
| Spatie Laravel Permission | Roles & Permissions |
| L5 Swagger | Swagger / OpenAPI Documentation |
| PHPUnit | Automated Testing |

---

# Assignment Requirements Covered

- Laravel Sanctum Authentication
- Users CRUD API
- Roles CRUD API
- Permissions CRUD API
- Pages CRUD API
- Dynamic Menu Management
- CKEditor Integration
- Cover Image Upload
- Form Request Validation
- API Resources
- Search
- Pagination
- Filtering
- Audit Fields
- Soft Delete
- Restore Deleted Pages
- Scheduled Publishing
- Custom Helper
- Swagger / OpenAPI Documentation
- Automated Feature Tests

---

# REST API

The CMS backend provides RESTful APIs for the following resources:

- Authentication
- User Management
- Role Management
- Permission Management
- Page Management
- Menu Management

All endpoints are documented using Swagger/OpenAPI and can be accessed through the integrated Swagger documentation after running the application.

---

# License

This project was developed as part of a Laravel Developer Technical Assignment.

---

# Author

**Saif Ali Ansari**

GitHub: https://github.com/SaifAliAnsari786