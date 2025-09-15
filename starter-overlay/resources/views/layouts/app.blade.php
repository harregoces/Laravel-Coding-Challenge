<!doctype html>
<html>
  <head>
    <title>Quotes</title>
    <style>
      body { font-family: system-ui, Arial, sans-serif; margin: 1.25rem; }
      nav a { margin-right: .75rem; }
      .btn { display:inline-block; padding: .4rem .6rem; border: 1px solid #ccc; border-radius: 4px; text-decoration:none; }
      form { display:inline; }
      img { max-width: 100%; height: auto; }
      .banner { background: #f4f6f8; border: 1px solid #e2e8f0; padding: .5rem .75rem; border-radius: 6px; margin: .5rem 0 1rem; display:inline-block;}
    </style>
  </head>
  <body>
    <nav>
      <a href="/today">Today</a>
      <a href="/quotes">Quotes</a>
      @auth
        <a href="/favorites">Favorites</a>
        <span>Logged in as {{ auth()->user()->email }}</span>
      @else
        <a href="/login">Login</a>
        <a href="/register">Register</a>
      @endauth
    </nav>
    <div class="banner">Client mode: <strong>{{ strtoupper(config('quotes.client', 'real')) }}</strong></div>
    <hr>
    <div class="content">
      @yield('content')
    </div>
  </body>
</html>
