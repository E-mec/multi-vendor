# Multi-Vendor E-Commerce Platform

A modular, API-first multi-vendor e-commerce backend built with **Laravel 13**, organized using a **Domain-Driven Design (DDD)** approach via the `nwidart/laravel-modules` package. The platform supports multiple vendors managing their own product inventories, secured through **JWT-based authentication**.
>  This project demonstrates a modular monolith architecture using Laravel with a focus on scalability, clean domain separation, and API-first design.
---

## Table of Contents

- [Multi-Vendor E-Commerce Platform](#multi-vendor-e-commerce-platform)
  - [Table of Contents](#table-of-contents)
  - [🔍 Overview](#-overview)
  - [🧰 Tech Stack](#-tech-stack)
  - [🏗️ Architecture](#️-architecture)
  - [📁 Project Structure](#-project-structure)
  - [📦 Modules](#-modules)
    - [Authentication Module](#authentication-module)
    - [Product Module](#product-module)
    - [Inventory Module](#inventory-module)
  - [⚙️ Key Implementations](#️-key-implementations)
    - [JWT Authentication (`tymon/jwt-auth`)](#jwt-authentication-tymonjwt-auth)
    - [Modular Architecture (`nwidart/laravel-modules`)](#modular-architecture-nwidartlaravel-modules)
    - [Data Transfer Objects (`spatie/laravel-data`)](#data-transfer-objects-spatielaravel-data)
    - [Helper Functions (`app/helper.php`)](#helper-functions-apphelperphp)
  - [🚀 Getting Started](#-getting-started)
    - [Prerequisites](#prerequisites)
    - [Installation](#installation)
  - [📜 Available Scripts](#-available-scripts)
  - [🧪 Testing](#-testing)
  - [🧠 Design Decisions & Assumptions](#-design-decisions--assumptions)
  - [📚 API Documentation](#-api-documentation)
  - [📄 License](#-license)

---

## 🔍 Overview

This project is a scalable **multi-vendor marketplace backend** where multiple sellers (vendors) can register, manage products, and track inventory. It is designed as a clean, modular monolith — each feature domain is self-contained as a Laravel Module with its own routes, controllers, models, migrations, and service providers.

The API-first design makes it ready to be consumed by any frontend (React, Vue, mobile apps, etc.) or used as a headless backend.

---

## 🧰 Tech Stack

| Layer | Technology |
|---|---|
| **Language** | PHP 8.3+ |
| **Framework** | Laravel 13 |
| **Authentication** | JWT (`tymon/jwt-auth`) |
| **Modular Architecture** | `nwidart/laravel-modules` |
| **Data Transfer Objects** | `spatie/laravel-data` |
| **Frontend Build Tool** | Vite |
| **Package Manager (JS)** | npm |
| **Testing** | PestPHP 4 |
| **Code Style** | Laravel Pint |
| **Database** | MySQL / SQLite (configurable via `.env`) |
| **ORM** | Eloquent |

---

## 🏗️ Architecture

The application follows a **Modular Monolith** pattern using Domain-Driven Design principles. Rather than placing all business logic in Laravel's default `app/` directory, each domain is encapsulated in its own self-contained module under the `Modules/` directory.

```
Multi-Vendor Platform
│
├── Core Laravel App (app/)       ← Shared infrastructure, helpers, base classes
│
└── Modules/
    ├── Authentication/           ← User registration, login, JWT token management
    ├── Product/                  ← Product CRUD, vendor product management
    └── Inventory/                ← Stock tracking, Order placement
```

This architecture means:
- Modules are **logically independent and can be developed/tested in isolation**
- Each module can be **enabled or disabled** via `modules_statuses.json`
- Business logic is **co-located** with its domain (routes, controllers, models, migrations all in one module)
- The system is **horizontally extensible** — new domains (e.g. Payments) can be added as new modules without touching existing code

---

## 📁 Project Structure

```
multi-vendor/
│
├── app/                          # Core application layer
│   ├── helper.php                # Global helper functions (autoloaded)
│   ├── Http/
│   │   ├── Controllers/          # Base/shared controllers
│   │   └── Middleware/           # Global middleware
│   ├── Models/                   # Shared Eloquent models (if any)
│   └── Providers/                # Application service providers
│
├── Modules/                      # All feature modules live here
│   ├── Authentication/
│   │   |
│   │   │── actions/          # Authentication and vendor actions (egs: CreateUserAction, CreateVendorAction)
│   │   │── Http/Controllers/ # Authentication and vendor controllers 
│   │   │── Models/           # User model
│   │   │── data/             # Spatie Data DTOs for user and vendor output
│   │   │── Providers/        # Module service provider
│   │   │
│   │   ├── database/
│   │   │   └── migrations/       # User table migrations
│   │   ├── resources/views/      # Module views (if any)
│   │   ├── tests/            # Feature and unit tests for module
│   │   └── routes/
│   │       └── api.php           # Auth API routes
│   │
│   ├── Product/
│   │   │
│   │   │── actions/          # Product actions (egs: CreateOrUpdateProductAction)
│   │   │── Http/Controllers/ # Product CRUD controllers for vendor , user amd guest
│   │   │── Http/Middlewares/ # Product route guard for vendor , user amd guest
│   │   │── dto/              # Spatie Data DTOs for product output
│   │   │── Models/           # Product Eloquent model
│   │   │── Policy/           # Product model authorization
│   │   │
│   │   ├── database/
│   │   │   └── migrations/       # Products table migrations
│   │   │── tests/           # Feature and unit tests for module
│   │   └── routes/
│   │       └── api.php           # Product API routes (JWT protected)
│   │
│   └── Inventory/
│       |
│       │── actions/          # Inventory management and Order actions (egs: HandleStockAction, CreateOrderAction)
│       │── Http/Controllers/ # Inventory management and Order controllers
│       │── Data/             # Spatie Data DTOs
│       │── Models/           # Order Eloquent model
│       │
│       ├── database/
│       │   └── migrations/       # Order table migrations
│       │── tests/           # Feature and unit tests for module
│       └── routes/
│           └── api.php           # Order and Inventory API routes (JWT protected)
│
├── bootstrap/                    # Laravel bootstrap files
├── config/                       # Application configuration files
├── database/
│   ├── factories/                # Eloquent model factories
│   ├── migrations/               # Core/shared migrations
│   └── seeders/                  # Database seeders
│
├── public/                       # Web server entry point (index.php, assets)
├── resources/
│   ├── css/                      # Global CSS
│   ├── js/                       # Global JavaScript (app.js)
│   └── views/                    # Global Blade views (if applicable)
│
├── routes/
│   ├── api.php                   # Core API routes
│   └── web.php                   # Web routes (minimal — API-first)
│
├── storage/                      # Logs, cache, uploaded files
├── stubs/nwidart-stubs/          # Custom stubs for generating new modules
├── tests/
│   ├── Feature/                  # Feature/integration tests
│   └── Unit/                     # Unit tests
│
├── modules_statuses.json         # Enable/disable modules
├── vite.config.js                # Vite frontend build configuration
├── vite-module-loader.js         # Custom Vite plugin to load module assets
├── composer.json                 # PHP dependencies
└── package.json                  # JS dependencies
```

---

## 📦 Modules

### Authentication Module

Handles all user identity and access management.

**Responsibilities:**
- User registration (vendor/customer sign-up)
- Login with credential validation
- JWT token issuance and refresh via `tymon/jwt-auth`
- Logout (token invalidation)
- Auth middleware protecting downstream module routes

**Key Routes (example):**
```
POST /api/auth/register
POST /api/auth/login
GET /api/auth/logout
```

---

### Product Module

Manages the product catalogue for all vendors.

**Responsibilities:**
- Create, read, update, and delete products
- Associate products with vendor (authenticated user)
- Data validation and transformation via `spatie/laravel-data` DTOs
- Paginated product listings

**Key Routes (vendor):**
```
GET    /api/vendor/products
POST   /api/vendor/products
GET    /api/vendor/products/{id}
PUT    /api/vendor/products/{id}
DELETE /api/vendor/products/{id}
```

**Vendor Order Response Structure:**
```
GET    /api/vendor/products:
{
    "status": "ok",
    "message": "Products",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "product updated",
                "stock_quantity": "500",
                "description": "new description",
                "price": 40,
                "status": "active",
                "vendor": {
                    "id": 1,
                    "store_name": "obeyluxus",
                    "user": null
                }
            },
            {
                "id": 2,
                "name": "new product 2",
                "stock_quantity": "50",
                "description": "new description",
                "price": 40,
                "status": "active",
                "vendor": {
                    "id": 1,
                    "store_name": "obeyluxus",
                    "user": null
                }
            }
        ],
        "first_page_url": "https://vendor_inventory_management.test/api/vendor/products?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "https://vendor_inventory_management.test/api/vendor/products?page=1",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "page": null,
                "active": false
            },
            {
                "url": "https://vendor_inventory_management.test/api/vendor/products?page=1",
                "label": "1",
                "page": 1,
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "page": null,
                "active": false
            }
        ],
        "next_page_url": null,
        "path": "https://vendor_inventory_management.test/api/vendor/products",
        "per_page": 15,
        "prev_page_url": null,
        "to": 2,
        "total": 2
    }
}

POST   /api/vendor/products:
{
    "status": "ok",
    "message": "Product Created",
    "data": {
        "id": 3,
        "name": "new product 3",
        "stock_quantity": "50",
        "description": "new description",
        "price": 40,
        "status": "active",
        "vendor": {
            "id": 1,
            "store_name": "obeyluxus",
            "user": null
        }
    }
}

GET    /api/vendor/products/{id}:
{
    "status": "ok",
    "message": "Product",
    "data": {
        "id": 3,
        "name": "new product 3",
        "stock_quantity": "50",
        "description": "new description",
        "price": 40,
        "status": "active",
        "vendor": {
            "id": 1,
            "store_name": "obeyluxus",
            "user": null
        }
    }
}

PUT    /api/vendor/products/{id}:

DELETE /api/vendor/products/{id}
```

**Key Routes (user or guest):**
```
GET    /api/products
GET    /api/products/{id}
```

---

### Inventory Module

Tracks stock levels per product per vendor and handles order placement.

**Responsibilities:**
- Maintain stock quantities for products
- Handle stock increment/decrement operations
- Link inventory records to the Product module
- Order products

**Key Routes (example):**
```
POST   /api/order

```
**Response Structure (example):**
```
Place Order Response
 {
    "status": "ok",
    "message": "Order placed",
    "data": {
        "id": 6,
        "product": {
            "id": 2,
            "name": "new product 2",
            "stock_quantity": "48",
            "description": "new description",
            "price": 40,
            "status": "active",
            "vendor": {
                "id": 1,
                "store_name": "obeyluxus",
                "user": null
            }
        },
        "quantity": 1,
        "total_price": 40
    }
}

```

---

## ⚙️ Key Implementations

### JWT Authentication (`tymon/jwt-auth`)

All protected API routes use JWT Bearer tokens. After a successful login, a token is returned and must be included in the `Authorization` header for all subsequent requests:

```
Authorization: Bearer <your_token>
```

The `auth:api` middleware (configured to use the JWT guard) is applied to the Product module routes.

---

### Modular Architecture (`nwidart/laravel-modules`)

Modules are loaded automatically via the `Modules/` namespace registered in `composer.json`:

```json
"autoload": {
    "psr-4": {
        "Modules\\": "Modules/"
    }
}
```

Module status is tracked in `modules_statuses.json`:

```json
{
    "Authentication": true,
    "Product": true,
    "Inventory": true
}
```

Setting a module to `false` disables it without removing code.

---

### Data Transfer Objects (`spatie/laravel-data`)

The Product and Inventory modules use `spatie/laravel-data` to define strongly-typed DTO classes for API responses. This enforces consistent data shapes across the API and keeps controllers thin:

```php
class ProductData extends Data
{
    public function __construct(
        public string $name,
        public string $description,
        public float $price,
    ) {}
}
```

---

### Helper Functions (`app/helper.php`)

A global helper file is autoloaded via Composer, providing shared utility functions available across all modules and the core application without needing to instantiate any class.

---

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.3
- Composer
- Node.js & npm
- A database (MySQL, SQLite, or PostgreSQL)

### Installation

**1. Clone the repository**
```bash
git clone https://github.com/E-mec/multi-vendor.git
cd multi-vendor
```

**2. One-command setup** (installs dependencies, copies `.env`, generates key, runs migrations, builds assets)
```bash
composer run setup
```

Or manually:

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure your database in .env, then:
php artisan migrate
npm install
npm run build
```

**3. Configure JWT secret**
```bash
php artisan jwt:secret
```

**4. Start the development server**
```bash
php artisan serve
```
This concurrently starts the Laravel server, queue listener, and Vite dev server.

---

## 📜 Available Scripts

| Command              | Description                               |
|----------------------|-------------------------------------------|
| `composer run setup` | Full first-time project setup             |
| `composer run dev`   | Start Laravel + Queue + Vite concurrently |
| `composer run test`  | Clear config cache and run all tests      |
| `php artisan test`   | Run all tests                             |
| `npm run dev`        | Start Vite dev server                     |
| `npm run build`      | Build frontend assets for production      |

---

## 🧪 Testing

The project uses **PestPHP 4** with the Laravel plugin for expressive, readable tests.

```bash
composer run test
# or
php artisan test
```

Tests are organized in:
- `tests/Feature/` — End-to-end API and integration tests
- `tests/Unit/` — Isolated unit tests for individual classes and functions

Each module can also contain its own `tests/` directory following the same Feature/Unit split.

---
## 🧠 Design Decisions & Assumptions

### 1. Modular Monolith Architecture

I chose a modular monolith using `nwidart/laravel-modules` instead of both a traditional monolith and microservices architecture:

- Compared to a traditional monolith:
    - Provides better separation of concerns through domain-based modules
    - Improves maintainability and scalability within a single codebase

- Compared to microservices:
    - Keeps deployment and infrastructure simple
    - Avoids distributed system complexity (network calls, service orchestration)

This approach offers a balanced middle ground, allowing the system to scale into microservices later if needed.

---

### 2. JWT Authentication
JWT (`tymon/jwt-auth`) was chosen over Laravel Sanctum because:
- The API is stateless
- It supports mobile and SPA clients easily
- No server-side session storage is required

---

### 3. API-First Design
The system is built as an API-first backend to:
- Allow flexibility for multiple frontends (web, mobile)
- Encourage separation of concerns

---

### 4. User & Vendor Separation

I chose to separate `users` and `vendors` into different tables instead of using a single vendor-only authentication model.

This was a deliberate architectural decision to separate identity from domain roles.

- The `users` table represents system identity (authentication layer)
- The `vendors` table represents a role/profile (business layer)

This allows:
- Users to exist without being vendors (e.g. customers or future roles)
- A user to become a vendor at any time without affecting authentication
- Clear separation between authentication logic and domain-specific data

This approach improves flexibility and aligns with real-world systems where:
- Not every user is a vendor
- Roles can evolve over time

#### Trade-offs
- Introduces additional relationships (user ↔ vendor)
- Slightly more complexity compared to a single-table design

#### Assumptions
- Each vendor is associated with exactly one user
- A user can exist without being a vendor

---

### 5. Vendor Ownership Model
Products are tied to authenticated vendors to ensure:
- Data isolation between vendors
- Authorization is enforced via policies and middleware

---

### 6. Inventory Handling
Stock is updated at the time of order placement:
- Assumes a single order flow (no cart/checkout system yet)
- Prevents overselling by reducing stock immediately

---

### 7. Assumptions
- Each product belongs to one vendor
- No payment gateway integration (orders are simulated)
- No role-based permissions beyond authentication

---

### 8. Key Trade-offs Summary

- Modular monolith improves structure but does not provide independent deployment like microservices
- JWT enables stateless APIs but introduces additional complexity around token lifecycle management
- Separating users and vendors increases flexibility but adds relational complexity

---

## 📚 API Documentation

Interactive API documentation (test endpoints, view schemas, and authentication flow):

👉 https://hzyjczfb91.apidog.io


This documentation includes:
- Authentication (JWT flow)
- Product module endpoints
- Inventory & order management
- Request/response schemas
- Note: If the API server is not running locally, the documentation will still display all endpoints and schemas, but live request execution may not work.

> Note: A sample of key endpoints and responses is included in this README for quick reference. For complete API coverage, use the Apidog documentation.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
