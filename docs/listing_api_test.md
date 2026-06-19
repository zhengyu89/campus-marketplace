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

Listing creation uses `multipart/form-data` so the optional image can be uploaded with the item
fields.

```http
POST /api/listings
Authorization: Bearer <token>
Content-Type: multipart/form-data

title=Engineering Mathematics Textbook
description=Clean copy with light notes in two chapters.
price=38.50
category_id=1
condition_status=Used
listing_status=Available
image=<JPG, PNG, or WebP file>
```

The image is optional. Expected: `201 Created`.

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

### Replace a listing image

```http
POST /api/listings/7/image
Authorization: Bearer <owner-token>
Content-Type: multipart/form-data

image=<JPG, PNG, or WebP file>
```

Expected: `200 OK`. The previous managed local image is deleted after the new image is saved.

### Remove a listing image

```http
DELETE /api/listings/7/image
Authorization: Bearer <owner-token>
```

Expected: `200 OK`. `image_url` becomes `null`, and a managed local file is deleted.

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
- Image larger than 5 MB
- Non-image file or unsupported image type
- Unsupported filter or sort value

## Image storage behaviour

- Accepted formats: JPG, PNG, and WebP
- Maximum application file size: 5 MB
- Generated filenames: random 32-character hexadecimal names
- Storage directory: `backend/public/uploads/listings/`
- Public path example: `/uploads/listings/abc123...png`
- Managed files are removed when replaced, explicitly removed, or deleted with their listing
- Seeded HTTP/HTTPS image URLs remain readable but are never deleted from external services
