<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
    <title>@yield('title', 'AgriTani')</title>
</head>
<body>
    <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center px-3 py-5">
        <a href="{{ route('home') }}" class="fs-2 fw-bold text-brand-dark text-decoration-none mb-4">🌱 AgriTani</a>
        <div class="card shadow-sm w-100" style="max-width: 28rem;">
            <div class="card-body p-4 p-sm-5">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
