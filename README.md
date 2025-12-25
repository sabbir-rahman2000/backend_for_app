# Campus Pre-owned Market - Backend API Documentation

## 📱 Project Overview

**Campus Pre-owned Market** is a comprehensive Laravel-based REST API for a university campus marketplace platform. It enables students to buy, sell, and trade pre-owned items with features like email verification, authentication, product management, wishlists, messaging, and transaction tracking.

**Built For:** Zhengzhou University  
**Framework:** Laravel 11  
**Authentication:** Laravel Sanctum  
**Database:** MySQL  
**Deployment:** Laravel Cloud  

---

## ✨ Core Features

### 🔐 Authentication & Security
- **User Registration** with required Student ID
- **Email Verification** using 6-digit codes (2-minute expiry)
- **Password Reset** with secure 6-digit codes (2-minute expiry)
- **Token-Based Auth** using Laravel Sanctum
- **Automatic Token Refresh** capability
- **Professional Email Templates** with University branding

### 📦 Product Management
- Create, read, update, delete products
- Product images with cloud storage
- Category classification
- Price management
- Product statistics (total count, sold count)
- Sold/Available status tracking

### 💬 Messaging System
- User-to-user messaging
- Real-time conversation tracking
- Message history retrieval

### ❤️ Wishlist System
- Add products to wishlist
- Remove items from wishlist
- View wishlist items
- Check if product is in wishlist

### 🛒 Transaction Management
- Track product sales/transactions
- Buyer and seller information
- Transaction history

### 👤 User Profiles
- Student ID required during registration
- Email and phone verification
- Profile information retrieval
- User product viewing

---

## 🔌 API Endpoints Summary

| # | Method | Endpoint | Auth Required | Purpose |
|---|--------|----------|---|---------|
| 1 | POST | `/api/auth/register` | No | Register new user |
| 2 | POST | `/api/auth/login` | No | User login |
| 3 | POST | `/api/auth/verify-email` | No | Verify email with code |
| 4 | POST | `/api/auth/resend-code` | No | Resend verification code |
| 5 | POST | `/api/auth/forgot-password` | No | Request password reset |
| 6 | POST | `/api/auth/reset-password` | No | Reset password with code |
| 7 | GET | `/api/auth/me` | Yes | Get current user profile |
| 8 | GET | `/api/auth/refresh-token` | Yes | Refresh API token |
| 9 | POST | `/api/auth/logout` | Yes | User logout |
| 10 | POST | `/api/products` | Yes | Create product |
| 11 | GET | `/api/products` | No | Get all products (paginated) |
| 12 | GET | `/api/products/{id}` | No | Get single product |
| 13 | GET | `/api/my-products` | Yes | Get my products |
| 14 | GET | `/api/my-products/stats` | Yes | Get product statistics |
| 15 | DELETE | `/api/products/{id}/delete` | Yes | Delete my product |
| 16 | GET | `/api/users/{user_id}/products` | No | Get user's products |
| 17 | GET | `/api/products/{product_id}/owner` | No | Get product owner info |
| 18 | GET | `/api/messages` | Yes | Get messages |
| 19 | POST | `/api/messages` | Yes | Send message |
| 20 | GET | `/api/wishlist` | Yes | Get my wishlist |
| 21 | POST | `/api/wishlist` | Yes | Add to wishlist |
| 22 | DELETE | `/api/wishlist/{product_id}` | Yes | Remove from wishlist |
| 23 | GET | `/api/wishlist/check/{product_id}` | Yes | Check if in wishlist |
| 24 | GET | `/api/users/{user_id}/wishlist-products` | Yes | Get user's wishlist |
| 25 | POST | `/api/sells` | Yes | Create transaction |
| 26 | GET | `/api/sells` | Yes | Get my transactions |
| 27 | GET | `/api/users/{id}` | Yes | Get user info |

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (for frontend)

### Step 1: Clone Repository
```bash
git clone https://github.com/sabbir-rahman2000/backend_for_app.git
cd backend_for_app
```

### Step 2: Install Dependencies
```bash
composer install
```

### Step 3: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Database Setup
Update `.env` with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=campus_market
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5: Run Migrations
```bash
php artisan migrate --force
```

### Step 6: Email Configuration
Configure mail settings in `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS="noreply@campus-market.edu"
MAIL_FROM_NAME="Campus Market"
```

### Step 7: Start Server
```bash
php artisan serve
# or for Laravel Cloud: git push origin main (auto-deployed)
```

---

## 📧 Email System

### Templates
**Location:** `resources/views/emails/verify.blade.php`

**Features:**
- Professional university branding
- Responsive mobile design
- Dual purpose (verification & password reset)
- Security notices
- 2-minute code expiry warning

### Email Types Sent:
1. **Email Verification** - On registration & resend
2. **Password Reset** - On forgot password request

---

## 🔐 Authentication Details

### Registration Requirements
- **name**: Min 2, max 255 characters
- **email**: Valid unique email
- **password**: Min 6 characters (must be confirmed)
- **phone**: 10-20 characters
- **student_id**: Min 6, max 50 characters (REQUIRED)

### Verification Flow
1. User registers → Verification code emailed (2-minute expiry)
2. User submits code → Email marked verified
3. User can resend code if needed

### Password Reset Flow
1. User requests forgot password → Reset code emailed (2-minute expiry)
2. User submits new password + reset code
3. Password updated and code cleared

### Token System
- **Sanctum API tokens** for authentication
- **Bearer token** in Authorization header required
- **Token refresh** endpoint available
- **Logout** revokes current token

---

## 💾 Database Schema Overview

### Key Tables
- **users** - Student accounts with verification & reset codes
- **products** - Marketplace items with seller info
- **messages** - User-to-user conversations
- **wishlists** - Saved favorite products
- **sells** - Transaction/sale records

---

## 🔑 Environment Variables

```env
APP_NAME="Campus Pre-owned Market"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxxxx

DB_CONNECTION=mysql
DB_HOST=your-host
DB_PORT=3306
DB_DATABASE=campus_market
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@campus-market.edu

SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173

MIGRATE_SECRET=your_secret_key
DEBUG_SECRET=your_debug_key
```

---

## 📁 Project Structure

```
backend_for_app/
├── app/Http/Controllers/        # Business logic
│   ├── AuthController.php        # Auth endpoints
│   ├── ProductController.php     # Product CRUD
│   ├── MessageController.php     # Messaging
│   ├── WishlistController.php    # Wishlist
│   ├── SellController.php        # Transactions
│   └── UserController.php        # User management
├── app/Models/                   # Database models
│   ├── User.php
│   ├── Product.php
│   ├── Message.php
│   ├── Wishlist.php
│   └── Sell.php
├── database/migrations/          # Schema migrations
├── resources/views/emails/       # Email templates
├── routes/api.php                # API routes
└── public/                        # Static files & uploads
```

---

## 🧪 Testing Endpoints

```bash
# Test API connection
GET /api/test

# Check database
GET /api/test-db

# Migration status
GET /api/run-migrations/ping
```

---

## 🛡️ Security Features

- ✅ Laravel Sanctum token authentication
- ✅ Bcrypt password hashing
- ✅ 6-digit codes with 2-minute expiry
- ✅ CORS protection
- ✅ Secure HTTP-only tokens
- ✅ Email verification required
- ✅ Password reset codes (not links)

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| Email not sending | Check MAIL_ config in .env and SMTP credentials |
| DB connection error | Run `GET /api/test-db` to diagnose |
| Migration failed | Use `POST /api/run-migrations` with X-Migrate-Secret header |
| Token expired | Call `GET /api/auth/refresh-token` |
| Can't verify email | Code expired? Use resend-code endpoint |

---

## 📚 Full Documentation

For complete endpoint details with request/response examples, see: [DETAILED_API.md](./DETAILED_API.md)

---

**Version:** 1.0.0  
**Last Updated:** December 26, 2025  
**Status:** ✅ Production Ready
