# Catmando Shoppe Craft - Sales Tracker

<p align="center">
  <img src="public/images/mainlogo.png" width="200" alt="Catmando Shoppe Craft Logo">
</p>

<p align="center">
  A simple and elegant sales tracking system for small handicraft businesses.
</p>

---

## About The Project

Catmando Shoppe Craft is a web application designed to help local artisans and small handicraft businesses manage their sales, inventory, and customer data with ease. This project provides a user-friendly interface to track business performance and streamline daily operations.

### Features

* **Dynamic Dashboard:** Get a quick overview of your business with dynamic stats and sales trends.
* **Sales Management:** Record and track all sales transactions.
* **Inventory Control:** Manage products, purchases, and stock movements in real-time.
* **Customer & Supplier Directory:** Keep a record of all your customers and suppliers.
* **Reporting:** Generate insightful reports to understand your business better.
* **Role-Based Access:** Secure your data with different user roles and permissions.

---

## Demo Login Credentials

You can use the following demo accounts to explore the system features.

| Role | Email | Password |
|-----|-----|-----|
| **Admin** | admin@example.com | 1234 |
| **Staff (Test User)** | test@example.com | test |

### Access Levels

- **Admin:** Full access to dashboard, inventory, sales, customers, suppliers, and reports.
- **Staff:** Limited access for daily operations such as processing sales and viewing inventory.

---

## Getting Started

Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

* PHP >= 8.1
* Composer
* Node.js & npm
* A web server (e.g., Apache, Nginx)
* A database (e.g., MySQL, PostgreSQL, SQLite)

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/HrishavKarmacharya/cat-pos.git
cd cat-pos
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install JavaScript dependencies

```bash
npm install
```

### 4. Create a copy of the `.env` file

```bash
cp .env.example .env
```

### 5. Generate an application key

```bash
php artisan key:generate
```

### 6. Configure your `.env` file

Update the database credentials and environment settings.

### 7. Run the database migrations and seeders

```bash
php artisan migrate --seed
```

### 8. Build the assets

```bash
npm run build
```

### 9. Start the development server

```bash
php artisan serve
```

Open in your browser:

```
http://127.0.0.1:8000
```

---

## Built With

* Laravel – Web application framework
* Livewire – Dynamic frontend components
* Tailwind CSS – UI styling
* Chart.js – Data visualization
* Vite – Frontend tooling
