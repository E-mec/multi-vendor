# Multi-Vendor E-Commerce Platform

A modular, API-first multi-vendor e-commerce backend built with **Laravel 13**, organized using a **Domain-Driven Design (DDD)** approach via the `nwidart/laravel-modules` package. The platform supports multiple vendors managing their own product inventories, secured through **JWT-based authentication**.

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
    └── Inventory/                ← Stock tracking, inventory updates
```

This architecture means:
- Modules are **independently developed and tested**
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
│   │   ├── app/
│   │   │   ├── Http/Controllers/ # Auth controllers (login, register, logout)
│   │   │   ├── Models/           # User model
│   │   │   └── Providers/        # Module service provider
│   │   ├── database/
│   │   │   └── migrations/       # User table migrations
│   │   ├── resources/views/      # Module views (if any)
│   │   └── routes/
│   │       └── api.php           # Auth API routes
│   │
│   ├── Product/
│   │   ├── app/
│   │   │   ├── Http/Controllers/ # Product CRUD controllers
│   │   │   ├── Data/             # Spatie Data DTOs for product input/output
│   │   │   └── Models/           # Product Eloquent model
│   │   ├── database/
│   │   │   └── migrations/       # Products table migrations
│   │   └── routes/
│   │       └── api.php           # Product API routes (JWT protected)
│   │
│   └── Inventory/
│       ├── app/
│       │   ├── Http/Controllers/ # Inventory management and Order controllers
│       │   ├── Data/             # Spatie Data DTOs
│       │   └── Models/           # Order Eloquent model
│       ├── database/
│       │   └── migrations/       # Order table migrations
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

Tracks stock levels per product per vendor and place order on products.

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

| Command | Description |
|---|---|
| `composer run setup` | Full first-time project setup |
| `composer run dev` | Start Laravel + Queue + Vite concurrently |
| `composer run test` | Clear config cache and run all tests |
| `npm run dev` | Start Vite dev server |
| `npm run build` | Build frontend assets for production |

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

---

## 📚 API Documentation

The full interactive API documentation is available via Apidog:

https://hzyjczfb91.apidog.io

This includes:
- All endpoints grouped by module (Authentication, Product, Inventory)
- Request/response schemas
- Authentication (JWT) usage
- Ability to test endpoints directly from the browser

> Note: A sample of key endpoints and responses is included in this README for quick reference. For complete API coverage, use the Apidog documentation.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
