# Personal Data Vault API & React Frontend

A secure, modern API and web app for managing your personal data vault. Built with Laravel (API) and React (frontend), featuring JWT authentication, encryption, category management, import/export, and a beautiful UI with dark mode.

---

## Features
- **JWT Authentication** (register, login, logout, profile, password change)
- **Personal Data CRUD** (add, edit, delete, search, filter)
- **Category Management** (add, edit, delete, filter)
- **CSV Import/Export** (with auto category creation)
- **Copy to Clipboard** (for any data value)
- **Reset Vault** (delete all data with confirmation)
- **Dark Mode** (toggle, persists)
- **Responsive, Minimal UI** (Tailwind CSS, toast notifications)
- **API Documentation** (Swagger/OpenAPI, Postman collection)

---

## Quick Start

### 1. Clone the Repo
```bash
git clone https://github.com/whohimanshukr/personal-data-vault-api.git
cd personal-data-vault-api
```

### 2. Backend Setup (Laravel)
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```
- The API will run at `http://localhost:8000`

### 3. Frontend Setup (React)
```bash
cd frontend
npm install
npm run dev
```
- The app will run at `http://localhost:5173`

### 4. Login
- Register a new user or use the demo:
  - **Email:** demo@example.com
  - **Password:** password123

---

## Usage
- **Add, edit, delete** your personal data and categories
- **Search/filter** by title or category
- **Import/export** your vault as CSV
- **Copy** any value with one click
- **Reset** your vault if needed
- **Switch** between light/dark mode

---

## API Documentation
- Swagger UI: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
- Postman: `Personal_Data_Vault_API.postman_collection.json`

---
## 🛠️ Tech Stack

**Backend:** Laravel, Sanctum, JWT, PostgreSQL  
**Frontend:** React, Vite, Tailwind CSS  
**Others:** Swagger, Postman, Docker 

---

## Credits
- **Himanshu Kumar** ([whohimanshukr](https://github.com/whohimanshukr))
- **AI Assistant** (OpenAI GPT-4, code, docs, and UI guidance)

---

## License
MIT
