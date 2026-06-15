## Manual Test Steps

1. Import `backend/database/schema.sql` and `backend/database/seed.sql`.
2. Start the backend with `composer serve`.
3. Start the frontend with `npm run dev`.
4. Open `http://localhost:5173`.
5. Log in with `registereduser1@gmail.com` / `User123!` and confirm the account page shows the `user` role.
6. Refresh `/account` and confirm the session stays active in the same browser session.
7. Log out and confirm `/account` redirects back to `/login`.
8. Log in with `admin@gmail.com` / `Admin123!` and confirm the account page shows the `admin` role.
9. Register a new user and confirm you are redirected to the login page.
10. Log in with the new user and confirm you land on `/account`.
