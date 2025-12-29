<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-base-url" content="{{ rtrim(request()->root(), '/') }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <title>@yield('title', 'Hệ thống thư viện')</title>
  <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">  
  <!-- CSS riêng cho view có thể push vào -->
  @stack('styles')
  <script>
    (() => {
      const base = document.querySelector('meta[name="app-base-url"]')?.content;
      if (!base) return;

      const normalizedBase = base.replace(/\/$/, '');

      const originalFetch = window.fetch?.bind(window);
      if (originalFetch) {
        window.fetch = (input, init) => {
          if (typeof input === 'string' && input.startsWith('/api/')) {
            input = normalizedBase + input;
          }
          return originalFetch(input, init);
        };
      }

      const originalOpen = window.open?.bind(window);
      if (originalOpen) {
        window.open = (url, target, features) => {
          if (typeof url === 'string' && url.startsWith('/api/')) {
            url = normalizedBase + url;
          }
          return originalOpen(url, target, features);
        };
      }
    })();
  </script>
</head>
<body>
  @yield('floating-shapes')

  @yield('content')

  @stack('scripts')
</body>
</html>