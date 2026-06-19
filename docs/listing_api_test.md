# Marketplace Listing API Test Guide

Base URL:

`http://localhost:8080/api`

Run the database setup before testing:

```bash
cd backend
composer db:setup
composer serve
```

The seed data includes sample listings and registered users. Use either:

- `registereduser1@gmail.com` / `User123!`
- `registereduser2@gmail.com` / `User123!`

## Public listing requests

### Browse listings

```http
GET /api/listings
```

Supported query parameters:

- `q`: title, description, category, or seller keyword
- `category_id`: numeric category ID
- `condition`: `New`, `Like New`, or `Used`
- `status`: `Available`, `Reserved`, or `Sold`
- `sort`: `newest`, `oldest`, `price_asc`, or `price_desc`
- `limit`: 1–100, used by the homepage recent-listing request

Example:

```http
GET /api/listings?q=calculator&status=Available&sort=price_asc
```

Expected: `200 OK` with `data.listings`.

### View one listing

```http
GET /api/listings/1
```

Expected: `200 OK` with `data.listing`, or `404 Not Found`.

## Protected listing requests

Log in first:

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "registereduser1@gmail.com",
  "password": "User123!"
}
```

Copy the returned token and add:

```http
Authorization: Bearer <token>
```

### View the authenticated seller’s listings

```http
GET /api/listings/mine
Authorization: Bearer <token>
```

Expected: `200 OK`. A missing or invalid token returns `401 Unauthorized`.

### Create a listing

```http
POST /api/listings
Authorization: Bearer <token>
Content-Type: application/json

{
  "title": "Engineering Mathematics Textbook",
  "description": "Clean copy with light notes in two chapters.",
  "price": 38.50,
  "category_id": 1,
  "image_url": "https://example.com/textbook.jpg",
  "condition_status": "Used",
  "listing_status": "Available"
}
```

Expected: `201 Created`.

Invalid fields return `422 Unprocessable Entity` with field errors.

### Edit a listing or update its status

Full edit:

```http
PUT /api/listings/7
Authorization: Bearer <owner-token>
Content-Type: application/json

{
  "title": "Engineering Mathematics Textbook",
  "description": "Updated description.",
  "price": 35,
  "category_id": 1,
  "image_url": "",
  "condition_status": "Used",
  "listing_status": "Available"
}
```

Status-only update:

```http
PUT /api/listings/7
Authorization: Bearer <owner-token>
Content-Type: application/json

{
  "listing_status": "Sold"
}
```

Expected: `200 OK`. A different registered user receives `403 Forbidden`.

### Delete a listing

```http
DELETE /api/listings/7
Authorization: Bearer <owner-token>
```

Expected:

- `200 OK` when the owner deletes a listing with no offers.
- `403 Forbidden` when another user attempts deletion.
- `409 Conflict` when the listing already has offer records.

## Validation scenarios

Confirm these return `422`:

- Empty title or description
- Zero or negative price
- Unknown category ID
- Unsupported condition or listing status
- Non-HTTP image URL such as `ftp://example.com/image.jpg`
- Unsupported filter or sort value
