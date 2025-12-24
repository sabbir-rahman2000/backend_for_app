# API Usage Guide

Base URLs:
- Local: http://127.0.0.1:8000/api
- Cloud: https://backend-for-app-main-hsw776.laravel.cloud/api

Headers:
- Accept: application/json
- Protected routes: Authorization: Bearer <token>

Endpoints
1) POST /auth/register
Body:
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
Returns: user + token; verification code emailed.

2) POST /auth/login
Body:
{
  "email": "john@example.com",
  "password": "secret123"
}
Returns: user + token.

3) POST /auth/verify-email
Body:
{
  "email": "john@example.com",
  "code": "123456"
}
Returns: email verified.

4) POST /auth/forgot-password
Body:
{
  "email": "john@example.com"
}
Returns: reset token (and email when configured).

5) POST /auth/reset-password
Body:
{
  "token": "<reset_token>",
  "email": "john@example.com",
  "password": "newpass123",
  "password_confirmation": "newpass123"
}
Returns: password reset success.

6) GET /auth/refresh-token (protected)
Header: Authorization: Bearer <old_token>
Returns: new token (old revoked).

Additional
- POST /auth/logout (protected): revoke current token.
- GET /auth/me (protected): current user info.
- GET /users (public): list users (name, email, phone).

Quick cURL examples
# Register
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Accept: application/json" \
  -d "name=Test User" \
  -d "email=user@example.com" \
  -d "password=secret123" \
  -d "password_confirmation=secret123"

# Login
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Accept: application/json" \
  -d "email=user@example.com" \
  -d "password=secret123" | jq -r '.data.token')

# Me (protected)
curl -H "Accept: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     http://127.0.0.1:8000/api/auth/me

Notes
- Use the cloud base URL for online testing; local for emulator/testing.
- Always include Bearer token on protected routes.
- If token expires, call /auth/refresh-token and replace stored token.
