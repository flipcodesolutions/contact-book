<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body>
    @include('partials.navbar')

    <main class="container py-4">
        @include('partials.flash')
        @yield('content')
    </main>

    @include('partials.bottom-nav')
    <script src="{{ asset('js/argil.js') }}"></script>
    <script>
        // The service worker only runs in a secure browser.
        // https:// is required in production. http://localhost is allowed for local testing.
        if ('serviceWorker' in navigator && window.isSecureContext) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
