# Offer Management API Test Guide

Base URL:

`http://localhost:8080/api`

Use two seeded user accounts:

- Seller: `registereduser1@gmail.com` / `User123!`
- Buyer: `registereduser2@gmail.com` / `User123!`

## 1. Login as Buyer

```http
POST /auth/login
Content-Type: application/json

{
  "email": "registereduser2@gmail.com",
  "password": "User123!"
}
```

Copy the returned JWT token.

## 2. Make an Offer

Use a listing owned by another user and currently marked `Available`.

```http
POST /listings/1/offers
Authorization: Bearer BUYER_TOKEN
Content-Type: application/json

{
  "offer_price": 40,
  "message": "Can collect this week."
}
```

Expected result:

- `201 Created`
- Response contains the new offer with `offer_status` set to `Pending`.

Validation and authorization checks:

- Own listing returns `403`.
- Sold or reserved listing returns `409`.
- A second offer by the same buyer for the same listing returns `409`.
- Invalid price or message longer than 255 characters returns `422`.

## 3. View Sent Offers

```http
GET /offers/mine
Authorization: Bearer BUYER_TOKEN
```

Expected result:

- `200 OK`
- Response contains offers sent by the logged-in user.

## 4. Edit or Delete a Sent Offer

Only the buyer who created the offer can edit or delete it.

```http
PUT /offers/1
Authorization: Bearer BUYER_TOKEN
Content-Type: application/json

{
  "offer_price": 42,
  "message": "Updated offer amount."
}
```

Expected result:

- `200 OK`
- Pending offer amount/message is updated.

```http
DELETE /offers/1
Authorization: Bearer BUYER_TOKEN
```

Expected result:

- `200 OK`
- Offer is removed.

Accepted offers cannot be deleted.

## 5. Login as Seller

```http
POST /auth/login
Content-Type: application/json

{
  "email": "registereduser1@gmail.com",
  "password": "User123!"
}
```

Copy the seller JWT token.

## 6. View Received Offers

```http
GET /offers/received
Authorization: Bearer SELLER_TOKEN
```

Expected result:

- `200 OK`
- Response contains offers for listings owned by the logged-in seller.

## 7. Accept or Reject an Offer

```http
POST /offers/1/accept
Authorization: Bearer SELLER_TOKEN
```

Accepting an offer:

- Sets the selected offer to `Accepted`.
- Rejects other pending offers for the same listing.
- Sets the listing status to `Reserved`.

Rejecting an offer:

```http
POST /offers/1/reject
Authorization: Bearer SELLER_TOKEN
```

Expected result:

- Selected pending offer becomes `Rejected`.

## 8. Cancel a Sent Offer

The delete endpoint is the preferred buyer action. The cancel endpoint remains available when a
buyer wants to mark a pending offer as cancelled instead of removing it.

```http
POST /offers/1/cancel
Authorization: Bearer BUYER_TOKEN
```

Expected result:

- Selected pending offer becomes `Cancelled`.
