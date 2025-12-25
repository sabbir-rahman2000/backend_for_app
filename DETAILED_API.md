# Campus Pre-owned Market - Detailed API Documentation

Complete endpoint reference with request/response examples.

---

## 🔐 Authentication Endpoints

### 1. Register User
**POST** `/api/auth/register`

Create a new student account with required verification.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@university.edu",
  "password": "SecurePass123",
  "password_confirmation": "SecurePass123",
  "phone": "9876543210",
  "student_id": "STU2025001"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully. Verification code sent to your email.",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@university.edu",
      "student_id": "STU2025001",
      "is_verified": false
    },
    "token": "1|abc123def456...",
    "email_sent": true
  }
}
```

**Validation Rules:**
- name: required, string, 2-255 chars
- email: required, unique, valid email format
- password: required, min 6 chars, must match confirmation
- phone: required, 10-20 chars
- student_id: required, 6-50 chars

---

### 2. Login
**POST** `/api/auth/login`

Authenticate with email and password.

**Request Body:**
```json
{
  "email": "john@university.edu",
  "password": "SecurePass123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@university.edu",
      "student_id": "STU2025001",
      "is_verified": true
    },
    "token": "1|abc123def456..."
  }
}
```

**Error Responses:**
- 401: Invalid credentials
- 422: Validation failed

---

### 3. Verify Email
**POST** `/api/auth/verify-email`

Complete email verification with 6-digit code.

**Request Body:**
```json
{
  "email": "john@university.edu",
  "verification_code": "123456"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Email verified successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@university.edu",
      "student_id": "STU2025001",
      "is_verified": true
    }
  }
}
```

**Error Cases:**
- 400: Invalid verification code
- 400: Email already verified
- 400: Verification code expired (2 minutes)
- 404: User not found

---

### 4. Resend Verification Code
**POST** `/api/auth/resend-code`

Request a new verification code if expired.

**Request Body:**
```json
{
  "email": "john@university.edu"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Verification code sent to your email",
  "data": {
    "email": "john@university.edu",
    "email_sent": true
  }
}
```

**Error Cases:**
- 400: Email already verified
- 404: User not found

---

### 5. Forgot Password
**POST** `/api/auth/forgot-password`

Request password reset code via email.

**Request Body:**
```json
{
  "email": "john@university.edu"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Password reset code sent to your email",
  "data": {
    "email": "john@university.edu",
    "reset_code": "654321",
    "expires_at": "2025-12-26 14:35:00",
    "email_sent": true
  }
}
```

**Note:** `reset_code` is included for testing. Remove in production.

---

### 6. Reset Password
**POST** `/api/auth/reset-password`

Complete password reset with code.

**Request Body:**
```json
{
  "email": "john@university.edu",
  "reset_code": "654321",
  "password": "NewPassword123",
  "password_confirmation": "NewPassword123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Password reset successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@university.edu"
    }
  }
}
```

**Error Cases:**
- 400: Invalid reset code or email
- 400: Reset code expired (2 minutes)
- 422: Validation failed

---

### 7. Get Current User Profile
**GET** `/api/auth/me`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "student_id": "STU2025001",
      "email": "john@university.edu",
      "is_verified": true
    }
  }
}
```

---

### 8. Refresh Token
**GET** `/api/auth/refresh-token`

Get a new API token.

**Headers:**
```
Authorization: Bearer {old_token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "token": "2|new_token_xyz...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@university.edu"
    }
  }
}
```

---

### 9. Logout
**POST** `/api/auth/logout`

Revoke current API token.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

---

## 📦 Product Endpoints

### 10. Create Product
**POST** `/api/products`

Create a new product listing.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "title": "iPhone 12 Pro",
  "category": "Electronics",
  "price": 45000,
  "description": "Excellent condition, minimal scratches, 6 months old"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 5,
    "user_id": 1,
    "title": "iPhone 12 Pro",
    "category": "Electronics",
    "price": 45000,
    "description": "Excellent condition, minimal scratches, 6 months old",
    "images": [],
    "sold": 0,
    "created_at": "2025-12-26T14:30:00Z",
    "updated_at": "2025-12-26T14:30:00Z"
  }
}
```

**Validation Rules:**
- title: required, 2-255 chars
- category: required, max 100 chars
- price: required, numeric, >= 0
- description: optional, string
- images: optional, array (max 5MB each)

---

### 11. Get All Products
**GET** `/api/products?page=1`

Get paginated list of all products.

**Query Parameters:**
- `page`: Page number (default: 1)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 5,
        "user_id": 1,
        "title": "iPhone 12 Pro",
        "category": "Electronics",
        "price": 45000,
        "description": "Excellent condition...",
        "images": [],
        "sold": 0,
        "created_at": "2025-12-26T14:30:00Z"
      }
    ],
    "per_page": 15,
    "total": 50
  }
}
```

---

### 12. Get Single Product
**GET** `/api/products/{id}`

Get detailed product information.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "user_id": 1,
    "title": "iPhone 12 Pro",
    "category": "Electronics",
    "price": 45000,
    "description": "Excellent condition...",
    "images": [],
    "sold": 0,
    "created_at": "2025-12-26T14:30:00Z",
    "updated_at": "2025-12-26T14:30:00Z"
  }
}
```

**Error Cases:**
- 404: Product not found

---

### 13. Get My Products
**GET** `/api/my-products`

Get all products created by authenticated user.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "user_id": 1,
      "title": "iPhone 12 Pro",
      "category": "Electronics",
      "price": 45000,
      "description": "...",
      "images": [],
      "sold": 0,
      "created_at": "2025-12-26T14:30:00Z"
    }
  ]
}
```

---

### 14. Get My Products Statistics
**GET** `/api/my-products/stats`

Get count of total and sold products.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user_id": 1,
    "total_count": 5,
    "sold_count": 2
  }
}
```

---

### 15. Delete Product
**DELETE** `/api/products/{id}/delete`

Delete your own product listing.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

**Error Cases:**
- 403: Not authorized to delete this product
- 404: Product not found

---

### 16. Get User's Products
**GET** `/api/users/{user_id}/products`

Get all products from specific user.

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "user_id": 2,
      "title": "Product Title",
      "price": 5000,
      "sold": 0,
      "created_at": "2025-12-26T14:30:00Z"
    }
  ]
}
```

---

### 17. Get Product Owner Info
**GET** `/api/products/{product_id}/owner`

Get seller information for a product.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Seller Name",
    "email": "seller@university.edu",
    "phone": "9876543210",
    "student_id": "STU2025002"
  }
}
```

---

## 💬 Message Endpoints

### 18. Get All Messages
**GET** `/api/messages`

Get user's message conversations.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "product_id": 5,
      "message": "Is this item still available?",
      "read_at": null,
      "created_at": "2025-12-26T14:00:00Z"
    }
  ]
}
```

---

### 19. Send Message
**POST** `/api/messages`

Send message to another user.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "receiver_id": 2,
  "product_id": 5,
  "message": "Is this item still available?"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "id": 1,
    "sender_id": 1,
    "receiver_id": 2,
    "product_id": 5,
    "message": "Is this item still available?",
    "read_at": null,
    "created_at": "2025-12-26T14:00:00Z"
  }
}
```

---

## ❤️ Wishlist Endpoints

### 20. Get My Wishlist
**GET** `/api/wishlist`

Get all wishlisted products.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "title": "iPhone 12 Pro",
      "category": "Electronics",
      "price": 45000,
      "sold": 0,
      "created_at": "2025-12-26T14:30:00Z"
    }
  ]
}
```

---

### 21. Add to Wishlist
**POST** `/api/wishlist`

Add product to wishlist.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "product_id": 5
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Product added to wishlist"
}
```

**Error Cases:**
- 409: Product already in wishlist
- 404: Product not found

---

### 22. Remove from Wishlist
**DELETE** `/api/wishlist/{product_id}`

Remove product from wishlist.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Product removed from wishlist"
}
```

---

### 23. Check if in Wishlist
**GET** `/api/wishlist/check/{product_id}`

Check if product is in wishlist.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "in_wishlist": true
  }
}
```

---

### 24. Get User's Wishlist Products
**GET** `/api/users/{user_id}/wishlist-products`

Get products from specific user's wishlist.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "title": "Product Title",
      "price": 45000,
      "sold": 0,
      "created_at": "2025-12-26T14:30:00Z"
    }
  ]
}
```

---

## 🛒 Transaction Endpoints

### 25. Create Transaction
**POST** `/api/sells`

Record a product sale/transaction.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "product_id": 5,
  "buyer_id": 2
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Sale recorded successfully",
  "data": {
    "id": 1,
    "product_id": 5,
    "seller_id": 1,
    "buyer_id": 2,
    "created_at": "2025-12-26T14:35:00Z"
  }
}
```

---

### 26. Get My Transactions
**GET** `/api/sells`

Get user's sell/transaction history.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 5,
      "seller_id": 1,
      "buyer_id": 2,
      "created_at": "2025-12-26T14:35:00Z"
    }
  ]
}
```

---

## 👤 User Endpoints

### 27. Get User Info
**GET** `/api/users/{id}`

Get detailed user information.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@university.edu",
    "phone": "9876543210",
    "student_id": "STU2025001",
    "is_verified": true,
    "created_at": "2025-12-26T10:00:00Z"
  }
}
```

---

## 🔄 Error Response Format

All errors follow this format:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["validation error message"]
  }
}
```

**HTTP Status Codes:**
- 200: Success
- 201: Created
- 400: Bad request / Validation error
- 401: Unauthorized
- 403: Forbidden
- 404: Not found
- 409: Conflict
- 422: Unprocessable entity
- 500: Server error

---

## 🔐 Common Headers

### Request Headers
```
Content-Type: application/json
Authorization: Bearer {token}  // For protected routes
```

### Response Headers
```
Content-Type: application/json
```

---

## 💡 Usage Examples

### Complete Registration Flow
```
1. POST /api/auth/register
   → Get token + "email_sent": true
   
2. Check email for 6-digit code
   
3. POST /api/auth/verify-email
   → Email marked verified
   
4. Can now use protected endpoints
```

### Password Reset Flow
```
1. POST /api/auth/forgot-password
   → Check email for 6-digit code
   
2. POST /api/auth/reset-password
   → Password updated
   
3. POST /api/auth/login
   → Login with new password
```

### Product Listing Flow
```
1. POST /api/products
   → Create new listing
   
2. GET /api/my-products
   → View your listings
   
3. GET /api/my-products/stats
   → Check sales stats
   
4. DELETE /api/products/{id}/delete
   → Remove listing
```

---

**API Version:** 1.0.0  
**Last Updated:** December 26, 2025  
**Status:** ✅ Production Ready
