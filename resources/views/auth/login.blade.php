<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IngeniaMath</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">

<div class="card p-4 shadow" style="width: 400px;">
    <h3 class="text-center mb-3">Iniciar Sesión</h3>
    <!-- Errores -->
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first('login') }}
        </div>
    @endif
    <form action="{{ route('login.store') }}" method="POST">
        @csrf
        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                required
            >
        </div>
        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input
                type="password"
                name="password"
                class="form-control"
                required
            >
        </div>
        <!-- Login -->
        <button type="submit" class="btn btn-primary w-100">
            Iniciar sesión
        </button>
    </form>
    <div class="text-center mt-3">
        <a href="{{ route('home') }}">← Volver</a>
    </div>

</div>

</body>
</html>
