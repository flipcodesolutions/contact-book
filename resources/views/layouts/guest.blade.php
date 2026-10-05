<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/argil-logo.svg') }}" alt="" width="48" height="48">
                    <h1 class="h3 mt-2 mb-0">Argil Leads</h1>
                </div>
                @include('partials.flash')
                <div class="card">
                    <div class="card-body p-4">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/argil.js') }}"></script>
</body>
</html>
