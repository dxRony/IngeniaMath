<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IngeniaMath</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f4f5;
        }

        .card {
            border: 1px solid #e4e4e7;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .brand {
            font-size: 1rem;
            font-weight: 600;
            color: #18181b;
            letter-spacing: -0.01em;
        }

        .brand span { color: #2563eb; }

        .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #18181b;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: #71717a;
        }

        .form-label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #3f3f46;
            margin-bottom: 6px;
        }

        .form-control {
            font-size: 0.9rem;
            border-color: #d4d4d8;
            border-radius: 8px;
            padding: 10px 13px;
            color: #18181b;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .forgot-link {
            font-size: 0.8125rem;
            color: #71717a;
            text-decoration: none;
        }

        .forgot-link:hover { color: #18181b; }

        .footer-text {
            font-size: 0.8125rem;
            color: #71717a;
        }

        .footer-text a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-text a:hover { text-decoration: underline; }

        .alert {
            font-size: 0.875rem;
            border-radius: 8px;
        }

        .divider {
            height: 1px;
            background: #e4e4e7;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

<div class="card p-4" style="width: 500px;">
    <div class="mb-4">
        <p class="brand mb-3">Ingenia<span>Math</span></p>
        <h1 class="page-title mb-1">Iniciar sesión</h1>
        <p class="page-subtitle mb-0">Ingresa tus datos para continuar</p>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger py-2">
        {{ $errors->first('login') }}
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success py-2">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('login.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" name="email" id="email" class="form-control"
                value="{{ old('email') }}" placeholder="tu@correo.com" required>
        </div>

        <div class="mb-1">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control"
                placeholder="••••••••" required>
        </div>

        <div class="text-end mb-3 mt-2">
            <a href="{{ route('password.request') }}" class="forgot-link">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
    </form>

    <div class="divider my-3"></div>

    <p class="footer-text text-center mb-1">
        ¿No tienes cuenta? <a href="{{ route('register.index') }}">Regístrate</a>
    </p>
    <p class="footer-text text-center mb-0">
        <a href="{{ route('home') }}" class="forgot-link">← Volver al inicio</a>
    </p>
</div>

</body>
</html>
