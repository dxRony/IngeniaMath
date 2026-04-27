<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'IngeniaMath')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .assessment-navbar {
            background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .assessment-navbar .brand-mark {
            color: #fbbf24;
            letter-spacing: 0.2px;
        }

        .assessment-navbar .exam-pill {
            background-color: rgba(251, 191, 36, 0.16);
            color: #fde68a;
            border: 1px solid rgba(251, 191, 36, 0.4);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-dark assessment-navbar @yield('navbar-color')">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check text-warning"></i>
                <span class="navbar-brand mb-0 fw-bold brand-mark">IngeniaMath</span>
            </div>

            <span class="exam-pill">
                Diagnóstico en curso
            </span>
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
