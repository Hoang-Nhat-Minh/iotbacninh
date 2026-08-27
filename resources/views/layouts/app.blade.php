@php
    $userSetting = Auth::check() ? Auth::user()->settings : null;
    $currentTheme = $userSetting->theme ?? 'light';
@endphp
<!DOCTYPE html>
<html lang="vi" data-theme="{{ $currentTheme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hệ Thống IoT Nông Nghiệp Bắc Ninh')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body>

<div class="app-container">
    @include('partials.sidebar')

    <div class="app-main">
        @include('partials.header')

        <main class="app-content">
            <div class="bg-bubble-container" aria-hidden="true">
                @for($i = 1; $i <= 20; $i++)
                    <div class="bg-bubble b-{{ $i }}"></div>
                @endfor
            </div>
            @yield('content')
        </main>

        @include('partials.footer')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if (session('success'))
            if (typeof showToast === 'function') {
                showToast("{{ addslashes(session('success')) }}", 'success');
            }
        @endif
        @if (session('error'))
            if (typeof showToast === 'function') {
                showToast("{{ addslashes(session('error')) }}", 'danger');
            }
        @endif
        @if ($errors->any())
            if (typeof showToast === 'function') {
                showToast("{{ addslashes($errors->first()) }}", 'danger');
            }
        @endif
    });
</script>
@stack('scripts')
</body>
</html>
