# UTM MarketHub High-Level Report

## Introduction

UTM MarketHub is a campus-focused marketplace platform developed to help the UTM community buy and sell second-hand items more easily. The system provides a centralized web platform where students can discover listings, create their own product posts, manage offers, and interact within a structured marketplace environment. The project is designed as a single-page web application with a Vue.js frontend and a PHP Slim REST API backend.

## Problem Background

Students often need affordable access to used items such as textbooks, electronics, furniture, and daily essentials. In many cases, buying and selling happen through scattered chat groups or social media posts, which makes the process disorganized, difficult to search, and hard to manage. Sellers may struggle to reach the right buyers, while buyers may miss relevant items or have no simple way to compare listings.

This creates several issues:

- Marketplace information is spread across multiple channels.
- Students cannot easily filter items by category, condition, or status.
- There is limited accountability when managing offers and ownership of listings.
- Administrators do not have a dedicated interface to manage marketplace categories.

## Proposed Solution

The proposed solution is UTM MarketHub, a centralized campus marketplace system that streamlines listing management, browsing, and offer handling in one platform. The system supports both public browsing and authenticated user actions, while also providing admin-only category management.

Key solution components include:

- A landing page that introduces the platform and highlights recent listings for the campus community. `[Screenshot of Home page]`
- A marketplace browsing page that allows users to search, filter, sort, and compare listings. `[Screenshot of Market Results page]`
- A listing detail and listing management flow where registered users can create, edit, and manage their own listings. `[Screenshot of Listing Detail page]` `[Screenshot of Create/Edit Listing page]`
- An offer management module that enables buyers to send offers and sellers to accept or reject them. `[Screenshot of Offers page]`
- An admin category management page for maintaining marketplace categories used across the system. `[Screenshot of Admin Categories page]`

At a high level, the system includes:

- User authentication with JWT-based login and protected actions.
- Public listing discovery for guests.
- Listing CRUD for registered users.
- Offer workflows between buyers and sellers.
- Category CRUD restricted to admin users.
- MySQL-backed storage for users, categories, listings, and offers.

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
- JWT Authentication
- PDO

### Database

- MySQL

## Impact

UTM MarketHub creates practical value for students by making second-hand buying and selling more accessible, organized, and efficient. Instead of depending on informal and fragmented communication channels, the system gives users a structured digital platform with better visibility and control over listings and offers.

The project also supports sustainable and community-oriented outcomes:

- **SDG 12: Responsible Consumption and Production** by encouraging the reuse and recirculation of goods such as books, electronics, and furniture instead of buying new items unnecessarily.
- **SDG 11: Sustainable Cities and Communities** by promoting a more resource-sharing campus environment and reducing waste within the university community.
- **SDG 8: Decent Work and Economic Growth** by helping students save money and create small peer-to-peer selling opportunities.

## Conclusion

UTM MarketHub addresses a real campus need by providing a focused marketplace platform for the UTM community. By combining listing management, offer handling, category administration, and secure authentication into one system, the project improves the overall buying and selling experience for students. It is both a functional web application and a practical contribution toward a more sustainable, connected, and efficient campus ecosystem.
