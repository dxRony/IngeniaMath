<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'IngeniaMath')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark @yield('navbar-color')">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-warning" href="@yield('home-route')">
                IngeniaMath
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                @auth
                @if(auth()->user()->is_active)
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @yield('menu')
                </ul>
                @else
                <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
                @endif
                @endauth

                @auth
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @if(auth()->user()->is_active)
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('profile.show') }}">Mi perfil</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link text-warning fw-bold">
                                Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
                @endauth

            </div>
        </div>
    </nav>

    <main class="container my-4 flex-grow-1">
        @yield('content')
    </main>

    <footer class="@yield('footer-color') text-white text-center py-3 mt-auto">
        <small>IngeniaMath - Proyecto TS2</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.MathJax = {
        tex: {
            inlineMath: [['\\(', '\\)'], ['$', '$']],
            displayMath: [['\\[', '\\]'], ['$$', '$$']]
        },
        svg: {
            fontCache: 'global'
        }
    };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>

</body>

</html>
