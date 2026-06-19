# Project Requirement Fulfilment Check

Status reflects the current repository after the Marketplace Listing module implementation.

| Requirement | Status | Evidence |
| --- | --- | --- |
| Vue.js 3 SPA | Fulfilled | Vue 3 views and reusable components rendered through Vue Router |
| Vite | Fulfilled | Frontend development and production build scripts |
| Bootstrap 5 | Fulfilled | Bootstrap grid, navigation, forms, buttons, and responsive utilities |
| Vue Router | Fulfilled | Public and protected routes without full-page reloads |
| Pinia | Fulfilled | Authentication and listing state stores |
| Axios / asynchronous calls | Fulfilled | Authentication, category, and listing requests use the Axios API client |
| PHP Slim REST API | Fulfilled | JSON authentication, category, and listing routes |
| MySQL relational database | Fulfilled | `users`, `categories`, `listings`, and `offers` tables |
| At least three related tables | Fulfilled | Four tables connected with foreign keys |
| PDO prepared statements | Fulfilled | Authentication, category, and listing repositories |
| GET, POST, PUT, DELETE | Fulfilled | Listing and category APIs collectively cover all required methods |
| Listing CRUD | Fulfilled | Browse, detail, create, owner edit, owner delete, and status updates |
| Listing search/filter | Fulfilled | Keyword, category, condition, status, and sorting |
| JWT authentication | Fulfilled | Login token, middleware, authenticated user, and protected APIs |
| Protected listing mutations | Fulfilled | JWT required for create, edit, status update, delete, and My Listings |
| Ownership authorization | Fulfilled | Only the listing seller can edit or delete; non-owner receives `403` |
| Frontend validation | Fulfilled | Listing, login, registration, and category forms validate before submission |
| Backend validation | Fulfilled | PHP services validate request data before database writes |
| Error handling/status codes | Fulfilled | `401`, `403`, `404`, `409`, and `422` listing scenarios |
| Category admin CRUD | Fulfilled | Public category reads and admin-protected category management |
| Database schema and seed | Fulfilled | Schema, migration-safe image column, users, categories, and sample listings |
| README setup documentation | Fulfilled | Local database, backend, frontend, and API instructions |
| Postman/API testing guide | Fulfilled | Authentication, category, and listing test guides in `docs/` |
| Offer Management module | Pending teammate module | Database table exists; send/view/accept/reject/cancel APIs and UI are not implemented |
| Laragon/XAMPP demonstration | Ready for local test | Setup commands and environment templates are provided |

## Listing module acceptance summary

- Guests can browse, search, filter, sort, and open listing details.
- Logged-in users can create listings and manage only their own listings.
- Admin users can browse listings but cannot modify another seller’s listing.
- Listing images use optional HTTP/HTTPS URLs with a frontend fallback.
- Listings with existing offers cannot be deleted, preserving relational integrity.
- The detail page reserves a clear integration area for the Offer module without exposing a broken action.
