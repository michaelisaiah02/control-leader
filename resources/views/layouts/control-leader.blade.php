<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Control Leader</title>
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />
  @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
  @yield('styles')
</head>

<body>
  <main class="container d-flex flex-column justify-content-between align-items-center vh-100 px-5">
    <header
      class="bg-primary d-flex w-100 justify-content-center align-items-center text-white text-center pt-4 pb-3 rounded-bottom-pill">
      <a href="/">
        <img src="{{ asset('image/logo-pt.png') }}" alt="Logo" class="mt-0 logo" />
      </a>
      <div class="mx-5">
        <h1 class="fs-1 fw-bold text-uppercase">Control Leader</h1>
        <p class="fs-2 lh-1">PT. CATURINDO AGUNGJAYA RUBBER</p>
        @yield('title-header')
      </div>
      <a href="/">
        <img src="{{ asset('image/logo-rice.png') }}" alt="Logo" class="mt-0 logo" />
      </a>
    </header>
    @yield('content')
    @yield('footer-action')
  </main>
</body>

</html>