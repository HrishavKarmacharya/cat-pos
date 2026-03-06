<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catmando Shoppe Craft</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-image: url('https://i.imgur.com/YG7Jf8l.jpeg'); /* a soft craft texture */
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 bg-opacity-40 backdrop-blur-sm">

    <!-- Navigation -->
    <header class="flex justify-between items-center px-8 py-6 bg-white bg-opacity-80 shadow-md">
        <h1 class="text-2xl font-bold text-gray-800 drop-shadow-sm">🧶Catmando Shoppe Craft🧶</h1>

        <div>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" 
                       class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                       Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="px-4 py-2 text-orange-700 hover:underline">
                       Log in
                    </a>

                    <a href="{{ route('register') }}" 
                       class="ml-4 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                       Register
                    </a>
                @endauth
            @endif
        </div>
    </header>

    <!-- Hero Section -->
    <section class="flex flex-col items-center justify-center text-center mt-20 px-6">

        <h2 class="text-5xl font-extrabold text-orange-800 drop-shadow-md">
            Discover the Magic of Catmando Handicrafts
        </h2>

        <p class="mt-4 text-xl text-gray-800 max-w-2xl">
            Explore our exquisite collection of handcrafted items, made with love and passion by local artisans. From felted wonders to intricate pottery, find the perfect piece to add a touch of Nepal to your life.
        </p>

        <a href="{{ route('login') }}"
           class="mt-8 px-8 py-3 bg-orange-700 text-white text-lg font-semibold rounded-lg shadow-lg hover:bg-orange-800">
            Explore Our Collection
        </a>
    </section>

    <!-- Features Section -->
    <section class="mt-24 grid grid-cols-1 md:grid-cols-3 gap-8 px-10 pb-20">

        <div class="bg-white bg-opacity-80 shadow-lg p-6 rounded-xl text-center">
            <h3 class="text-xl font-bold text-orange-700">🛍️ Seamless Sales Tracking</h3>
            <p class="mt-2 text-gray-700">
                Effortlessly manage your sales, customers, and payments in one place. Get detailed reports and insights to grow your business.
            </p>
        </div>

        <div class="bg-white bg-opacity-80 shadow-lg p-6 rounded-xl text-center">
            <h3 class="text-xl font-bold text-orange-700">📦 Smart Inventory Management</h3>
            <p class="mt-2 text-gray-700">
                Keep track of your stock levels in real-time. Get alerts for low stock and manage your inventory with ease.
            </p>
        </div>

        <div class="bg-white bg-opacity-80 shadow-lg p-6 rounded-xl text-center">
            <h3 class="text-xl font-bold text-orange-700"> Authentic Nepali Handicrafts</h3>
            <p class="mt-2 text-gray-700">
                All our products are handmade by skilled artisans in Nepal. We are committed to fair trade and supporting local communities.
            </p>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="bg-white bg-opacity-90 py-20 px-10">
        <h2 class="text-4xl font-extrabold text-center text-orange-800 mb-12">Featured Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="text-center">
                <img src="{{ asset('images/Singingbowl.jpg') }}" alt="Singing Bowl" class="w-full h-64 object-cover rounded-lg shadow-md">
                <h3 class="mt-4 text-xl font-semibold text-gray-800">Singing Bowl</h3>
                <p class="text-gray-600">Hand-hammered brass bowl for meditation and sound healing.</p>
            </div>
            <div class="text-center">
                <img src="{{ asset(path: 'images/basket.png') }}" alt="Basket" class="w-full h-64 object-cover rounded-lg shadow-md">
                <h3 class="mt-4 text-xl font-semibold text-gray-800">Basket</h3>
                <p class="text-gray-600">A charming, handcrafted basket made from 100% natural Bamboo(Doko).</p>
            </div>
            <div class="text-center">
                <img src="{{ asset(path: 'images/MithilaPainting.png') }}" alt="Mithila Painting" class="w-full h-64 object-cover rounded-lg shadow-md">
                <h3 class="mt-4 text-xl font-semibold text-gray-800">Mithila Painting</h3>
                <p class="text-gray-600">An intricate, hand-painted Mithila thangka for your sacred space.</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 px-10">
        <h2 class="text-4xl font-extrabold text-center text-orange-800 mb-12">What Our Customers Say</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 max-w-4xl mx-auto">
            <div class="bg-white bg-opacity-80 p-6 rounded-xl shadow-lg">
                <p class="text-gray-700">"I absolutely adore the Basket I bought! The craftsmanship is amazing, and it adds such a cozy touch to my home. I'll definitely be back for more."</p>
                <p class="mt-4 font-bold text-orange-700">-Bimlesh.</p>
            </div>
            <div class="bg-white bg-opacity-80 p-6 rounded-xl shadow-lg">
                <p class="text-gray-700">"The Mithila Painting is even more beautiful in person. The attention to detail is incredible. It's the perfect centerpiece for my meditation room."</p>
                <p class="mt-4 font-bold text-orange-700">-Gaganchad.</p>
            </div>
        </div>

    </section>



    <!-- Footer -->
    <footer class="bg-orange-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="space-y-4">
                    <h3 class="text-lg font-bold">Catmando Shoppe Craft</h3>
                    <p class="text-sm">Authentic Nepali handicrafts, straight from the artisans to you.</p>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-bold">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:underline">About Us</a></li>
                        <li><a href="#" class="hover:underline">FAQ</a></li>
                        <li><a href="#" class="hover:underline">Shipping & Returns</a></li>
                    </ul>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-bold">Contact</h3>
                    <p class="text-sm">Nurshing Chowk , Thamel , Kathmandu, Nepal</p>
                    <p class="text-sm">contact@catmandoshoppe.com</p>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-bold">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-white hover:text-gray-300">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="text-white hover:text-gray-300">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8zm-2-7h4v4h-4v-4zm-2-4h8v2h-8V9z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="text-white hover:text-gray-300">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.71v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="mt-8 border-t border-orange-700 pt-8 text-center text-sm">
                <p>&copy; 2025 Catmando Shoppe Craft. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
