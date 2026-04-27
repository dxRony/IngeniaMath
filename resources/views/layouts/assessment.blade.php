<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'IngeniaMath')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-dark @yield('navbar-color')">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold text-warning">IngeniaMath</span>
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
        svg: { fontCache: 'global' }
    };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>
    @stack('scripts')
</body>

</html>
