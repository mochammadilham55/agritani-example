@if (session('success'))
    <div class="alert alert-success py-2 small" role="alert">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger py-2 small" role="alert">{{ session('error') }}</div>
@endif
@if (session('warning'))
    <div class="alert alert-warning py-2 small" role="alert">{{ session('warning') }}</div>
@endif
