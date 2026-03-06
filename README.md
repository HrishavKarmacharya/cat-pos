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

*   **Dynamic Dashboard:** Get a quick overview of your business with dynamic stats and sales trends.
*   **Sales Management:** Record and track all sales transactions.
*   **Inventory Control:** Manage products, purchases, and stock movements in real-time.
*   **Customer & Supplier Directory:** Keep a record of all your customers and suppliers.
*   **Reporting:** Generate insightful reports to understand your business better.
*   **Role-Based Access:** Secure your data with different user roles and permissions.

---

## Getting Started

Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

*   PHP >= 8.1
*   Composer
*   Node.js & npm
*   A web server (e.g., Apache, Nginx)
*   A database (e.g., MySQL, PostgreSQL, SQLite)

### Installation

1.  **Clone the repository:**
    ```sh
    git clone https://github.com/your-username/catmando-shoppe-craft.git
    cd catmando-shoppe-craft
    ```

2.  **Install PHP dependencies:**
    ```sh
    composer install
    ```

3.  **Install JavaScript dependencies:**
    ```sh
    npm install
    ```

4.  **Create a copy of the `.env` file:**
    ```sh
    cp .env.example .env
    ```

5.  **Generate an application key:**
    ```sh
    php artisan key:generate
    ```

6.  **Configure your `.env` file** with your database credentials and other environment settings.

7.  **Run the database migrations and seeders:**
    ```sh
    php artisan migrate --seed
    ```

8.  **Build the assets:**
    ```sh
    npm run build
    ```

9.  **Start the development server:**
    ```sh
    php artisan serve
    ```

---

## Built With

*   [Laravel](https://laravel.com/) - The web application framework used.
*   [Livewire](https://laravel-livewire.com/) - For building dynamic interfaces.
*   [Tailwind CSS](https://tailwindcss.com/) - For styling the application.
*   [Chart.js](https://www.chartjs.org/) - For creating interactive charts.
*   [Vite](https://vitejs.dev/) - For frontend tooling.
