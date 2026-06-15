# Category/Admin Module API Test Notes

These steps cover Teow Zi Xian's Category/Admin and database module.

## Prerequisites

1. Run `composer db:setup` from `backend/`.
2. Start the backend with `composer serve`.
3. Log in as the seeded admin account:

```http
POST http://localhost:8080/api/auth/login
Content-Type: application/json

{
  "email": "admin@gmail.com",
  "password": "Admin123!"
}
```

Copy the returned JWT token and use it as:

```http
Authorization: Bearer <token>
```

## Category Endpoints

### View Categories

```http
GET http://localhost:8080/api/categories
```

Expected: `200 OK` with a `categories` array.

### Add Category

```http
POST http://localhost:8080/api/categories
Authorization: Bearer <admin-token>
Content-Type: application/json

{
  "category_name": "Transport"
}
```

Expected: `201 Created` with the new category.

### Update Category

```http
PUT http://localhost:8080/api/categories/1
Authorization: Bearer <admin-token>
Content-Type: application/json

{
  "category_name": "Books and Notes"
}
```

Expected: `200 OK` with the updated category.

### Delete Category

```http
DELETE http://localhost:8080/api/categories/1
Authorization: Bearer <admin-token>
```

Expected: `200 OK` if no listings are using the category.

## Authorization Checks

- `POST`, `PUT`, and `DELETE` without a token should return `401`.
- `POST`, `PUT`, and `DELETE` with a non-admin user token should return `403`.
- Duplicate category names should return `409`.
- Empty category names should return `422`.
