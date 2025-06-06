# Laravel + PostgreSQL Authentication System

## 📖 Overview

This project is a secure and scalable backend system for user registration and authentication using Laravel 12 and PostgreSQL. It implements a **5-step signup wizard**, **2FA**, **secure login/logout**, and adheres to best practices in input validation, throttling, token security, and session management.

The system ensures:

* Progressive registration with data persistence
* Country-specific phone formatting
* Two-Factor Authentication via email
* Brute-force protection and token-based session management

---

## 🚀 Features

* ✅ Multi-Step Registration Wizard
* ✅ Unique identifier for incomplete registration
* ✅ Auto country code detection for phone number
* ✅ Step-by-step data persistence with validation
* ✅ Email-based Two-Factor Authentication (2FA)
* ✅ Secure password setup
* ✅ Full review & confirmation before final submission
* ✅ Secure Login with Laravel Sanctum
* ✅ Throttling & brute-force attack protection
* ✅ Logout & token invalidation

---

## 🧱 Requirements

* PHP >= 8.2
* Composer
* PostgreSQL >= 14
* Laravel 12

---

## ⚙️ Setup Instructions

### 1. Clone the Repository

```bash
git clone git@github.com:cyubahiro367/software-challenge-auth-system.git
cd software-challenge-auth-system
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Copy Environment File

```bash
cp .env.example .env
```

### 4. Set Environment Variables

Edit the `.env` file with your PostgreSQL and Mail credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_auth
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_user
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=auth@example.com
MAIL_FROM_NAME="LaravelAuth"
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Enter PostgreSQL Shell

```bash
psql -U postgres
```
### 7. Create PostgreSQL Database

```sql
CREATE DATABASE laravel_auth;
```

### 8. Run Migrations

```bash
php artisan migrate
```

### 9. Start Development Server

```bash
php artisan serve
```

---

## 🛠️ API Endpoints

All routes are prefixed under `/api/auth/register`

### 📌 Registration Flow

| Step / Action                | Method | Endpoint                                         | Description                                 |
|------------------------------|--------|--------------------------------------------------|---------------------------------------------|
| Start Registration           | POST   | `/api/auth/register/start`                       | Begin registration, returns identifier      |
| Resume Registration          | GET    | `/api/auth/register/resume/{identifier}`         | Resume incomplete registration              |
| Step 1: Personal Info        | POST   | `/api/auth/register/step-1/{identifier}`         | Submit personal info                        |
| Step 2: Address              | POST   | `/api/auth/register/step-2/{identifier}`         | Submit address info                         |
| Step 3: Send 2FA Code        | POST   | `/api/auth/register/step-3/{identifier}`         | Send 2FA code to email                      |
| Verify 2FA                   | POST   | `/api/auth/register/verify-2fa/{identifier}`     | Verify 2FA code                             |
| Step 4: Password             | POST   | `/api/auth/register/step-4/{identifier}`         | Set password                                |
| Step 5: Review & Confirm     | POST   | `/api/auth/register/step-5/{identifier}`         | Review and confirm registration             |
| Upload Profile Picture       | POST   | `/api/auth/register/upload/profile-picture/{identifier}` | Upload profile picture (PNG only)   |
| Complete Registration        | POST   | `/api/auth/register/complete/{identifier}`       | Finalize registration                       |

### 🔐 Authentication

| Feature | Method | Endpoint           |
| ------- | ------ | ------------------ |
| Login   | POST   | `/api/auth/login`  |
| Logout  | POST   | `/api/auth/logout` |

---

## ✨ Registration Design Summary

### Step 1: Personal Info

* Honorific (nullable): Mr., Mrs., Miss, Ms., Dr., Prof., Hon.
* Auto-default: Mr. for male, Ms. for female
* Required: First name, Last name, Gender, DOB, Email, Nationality, Phone
* Unique: Email & Phone
* Email must not be from disposable providers
* PNG only for optional profile\_picture

### Step 2: Address

* Required: Country of residence, City, Postal code
* Optional: Apartment name, Room number
* If nationality ≠ residence: mark user as expatriate

### Step 3: Two-Factor Auth

* Sends a 6-digit code to the user's email
* Code valid for 10 minutes

### Step 4: Password

* Secure password setup and hashing
* Follows Laravel's password validation best practices

### Step 5: Review

* Show all previous steps
* Allow correction before final confirmation

---

## 🔐 Login & Security

* Secure login with email/password
* Returns access token using Sanctum
* Implements Laravel throttling to prevent brute-force
* Secure logout that invalidates the access token

---

## ✅ Deliverables Summary

* ✅ GitHub repository with all branches
* ✅ `.env.example` with required variables
* ✅ Database migrations
* ✅ DTOs, Enums, Requests for all steps
* ✅ README with setup instructions

