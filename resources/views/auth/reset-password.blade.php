<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - IngeniaMath</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-light">

    <div class="card p-4 shadow" style="width: 460px;">
        <h3 class="text-center mb-3">Nueva contraseña</h3>

        @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label for="email" class="form-label">Correo</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Nueva contraseña</label>
                <input type="password" id="password" name="password"
                    class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                    autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Restablecer contraseña</button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login.index') }}">Volver al login</a>
        </div>
    </div>

</body>

</html>
