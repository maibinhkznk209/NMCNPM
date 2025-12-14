<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <title>@yield('title', 'Hệ thống thư viện')</title>
  <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">  
  <!-- CSS riêng cho view có thể push vào -->
  @stack('styles')
</head>
<body>
  @yield('floating-shapes')

  @yield('content')

  @yield('scripts')
  @stack('scripts')
</body>
</html>