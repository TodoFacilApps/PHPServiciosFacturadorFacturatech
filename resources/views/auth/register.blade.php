<!DOCTYPE html>
<html>
<head>
    <title>Formulario de Registro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Formulario de Registro</h1>
        <form method="post" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label for="Nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="Nombre" id="Nombre" required>
            </div>
            <div class="mb-3">
                <label for="Apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" name="Apellido" id="Apellido" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Login</label>
                <input type="text" class="form-control" name="email" id="email" required>
            </div>
            <div class="mb-3">
                <label for="Correo" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" name="Correo" id="Correo" required>
            </div>
            <div class="mb-3">
                <label for="Contraseña" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="Contraseña" id="Contraseña" required>
            </div>
            <div class="mb-3">
                <label for="Telefono" class="form-label">Teléfono</label>
                <input type="tel" class="form-control" name="Telefono" id="Telefono">
            </div>
            <button type="submit" class="btn btn-primary">Registrarse</button>
        </form>
    </div>
</body>
</html>
