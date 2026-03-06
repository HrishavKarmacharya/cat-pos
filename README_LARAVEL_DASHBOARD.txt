
Steps to setup the Dashboard:

1. Follow the previous steps to set up the project and install Jetstream.
2. Copy the above files into your Laravel project directory.
3. Ensure Livewire is included:
    - @livewireStyles and @livewireScripts should be in `resources/views/layouts/app.blade.php`.
4. Register `DashboardStats` component in `resources/views/dashboard.blade.php`:
    - @livewire('dashboard-stats')
5. Run migration and seeders:
    php artisan migrate
    php artisan db:seed
6. You will see a dynamic dashboard at `/dashboard`.
