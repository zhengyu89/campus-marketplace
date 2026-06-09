# Campus Marketplace System

A simple Carousell-style campus marketplace system using Vue.js 3 frontend and PHP Slim backend.

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

Create and import:

```sql
SOURCE backend/database/schema.sql;
SOURCE backend/database/seed.sql;
```

### 2. Backend

```bash
cd backend
copy .env.example .env
composer install
```

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

## Modules

- Authentication Module
- Marketplace Listing Module
- Category Management Module
- Offer Management Module

## Team Workflow

Each member must create their own branch and open a pull request before merging into `main`.
