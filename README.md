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

## Marketplace Listing API

Listing reads are public. Creating and managing listings requires a registered user or admin JWT,
and only the original seller can edit or delete a listing.

- `GET /api/listings`
- `GET /api/listings/{id}`
- `GET /api/listings/mine`
- `POST /api/listings`
- `PUT /api/listings/{id}`
- `DELETE /api/listings/{id}`

Browse filters:

- `q`
- `category_id`
- `condition`
- `status`
- `sort=newest|oldest|price_asc|price_desc`

Frontend listing pages:

- `http://localhost:5173/market`
- `http://localhost:5173/listings/{id}`
- `http://localhost:5173/listings/create`
- `http://localhost:5173/listings/{id}/edit`
- `http://localhost:5173/my-listings`

The `listings.image_url` field is optional and accepts HTTP/HTTPS URLs. Running
`composer db:setup` or `composer db:schema` adds the column to an existing local database when
needed.

Detailed API examples are in `docs/listing_api_test.md`.

## Category/Admin API

Category reads are public, while write operations require an admin JWT token.

- `GET /api/categories`
- `POST /api/categories`
- `PUT /api/categories/{id}`
- `DELETE /api/categories/{id}`

The admin category management page is available at:

`http://localhost:5173/admin/categories`

Additional Postman/API testing notes are in:

- `docs/auth_test.md`
- `docs/category_admin_test.md`
- `docs/listing_api_test.md`

The complete assignment requirement status is in `docs/requirement_check.md`.

## Manual Test Steps

1. Run `composer db:setup`.
2. Start the backend with `composer serve`.
3. Start the frontend with `npm run dev`.
4. Open `http://localhost:5173`.
5. Browse `/market` without logging in and test keyword/category/status filters.
6. Open a listing detail page without logging in.
7. Log in with `registereduser1@gmail.com` / `User123!`.
8. Create a listing, edit it, and update its status from `/my-listings`.
9. Log in as a second registered user and confirm the first user’s edit API returns `403`.
10. Log out and confirm protected listing pages redirect to `/login`.
11. Log in with `admin@gmail.com` / `Admin123!` and confirm category administration still works.
12. Register a new user and confirm the account can create its own listing.

## Modules

- Authentication Module
- Marketplace Listing Module
- Category Management Module
- Offer Management Module

## Team Workflow

Each member must create their own branch and open a pull request before merging into `main`.
