<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IngeniaMath</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<main class="flex-grow-1 d-flex align-items-center">
    <div class="container text-center">

        <h1 class="display-3 fw-bold">
            ¡Bienvenido a <span class="text-primary">IngeniaMath</span>!
        </h1>

        <p class="lead mt-3">
            Plataforma de aprendizaje adaptativo para aspirantes a la Facultad de Ingeniería USAC,
            diseñada para ayudarte a mejorar tus habilidades matemáticas mediante práctica guiada,
            simulacros y seguimiento personalizado.
        </p>

        <a href="{{ route('login.index') }}" class="btn btn-primary mt-4">
            Iniciar sesión
        </a>

    </div>
</main>

<footer class="bg-light text-center py-3">
    <small>IngeniaMath - Proyecto TS2</small>
</footer>

</body>
</html>
