# UTM MarketHub

A simple Carousell-style UTM marketplace platform using Vue.js 3 frontend and PHP Slim backend.

## Tech Stack

### Frontend
- Vue.js 3
- Vite
- Vue Router
- Pinia
- Bootstrap 5
- Axios

### Backend
- PHP Slim Framework
- MySQL
- PDO
- JWT Authentication

## Project Structure

- `frontend/` - Vue.js single-page application
- `backend/` - PHP Slim REST API
- `backend/database/schema.sql` - Database structure
- `backend/database/seed.sql` - Sample seed data

## Local Setup

### 1. Database

Start Laragon or XAMPP MySQL.

Run the database setup:

```bash
cd backend
composer db:setup
```

Optional commands:

- `composer db:schema` - import only `database/schema.sql`
- `composer db:seed` - import only `database/seed.sql`

### 2. Backend

```bash
cd backend
copy .env.example .env
composer install
```
And update the JWT_SECRET.

Backend auth environment values:

- `JWT_SECRET` - secret key used to sign JWT tokens
- `JWT_TTL=86400` - token lifetime in seconds

### 3. Frontend

Open a second terminal:

```bash
cd frontend
copy .env.example .env
npm install
```

## After Setup

### Start Backend

```bash
cd backend
composer serve
```

Backend health check:

`http://localhost:8080/api/health`

### Start Frontend

```bash
cd frontend
npm run dev
```

Frontend URL:

`http://localhost:5173`

## Seeded Admin

Use this account after importing `backend/database/seed.sql`:

- Email: `admin@gmail.com`
- Password: `Admin123!`
- Role: `admin`

## Seeded Registered Users

The seed file also creates 10 registered user accounts:

- Emails: `registereduser1@gmail.com` to `registereduser10@gmail.com`
- Shared password: `User123!`
- Role: `user`

## Authentication API

- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me`

Protected requests use `Authorization: Bearer <token>`.

## Manual Test Steps

1. Run `composer db:setup`.
2. Start the backend with `composer serve`.
3. Start the frontend with `npm run dev`.
4. Open `http://localhost:5173`.
5. Log in with `registereduser1@gmail.com` / `User123!` and confirm the account page shows the `user` role.
6. Log out and confirm `/account` redirects back to `/login`.
7. Log in with `admin@campus.local` / `Admin123!` and confirm the account page shows the `admin` role.
8. Register a new user and confirm you are redirected to the login page.
9. Log in with the new user and confirm you land on `/account`.

## Modules

- Authentication Module
- Marketplace Listing Module
- Category Management Module
- Offer Management Module

## Team Workflow

Each member must create their own branch and open a pull request before merging into `main`.
