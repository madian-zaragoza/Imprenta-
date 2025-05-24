<?php
session_start();
include 'Conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    // Buscar en Administrador
    $queryAdmin = $conn->prepare("SELECT Contraseña FROM administrador WHERE Nombre = ? AND Correo = ?");
    $queryAdmin->bind_param("ss", $nombre, $correo);
    $queryAdmin->execute();
    $queryAdmin->store_result();

    if ($queryAdmin->num_rows > 0) {
        $queryAdmin->bind_result($hash);
        $queryAdmin->fetch();

        if (password_verify($contraseña, $hash)) {
            $_SESSION['admin'] = $nombre;
            header("Location: ProcesarPanel.php");
            exit();
        } else {
            echo "<p style='color:red;'>Contraseña incorrecta para administrador.</p>";
            exit();
        }
    }

    // Buscar en usuario
    $queryUser = $conn->prepare("SELECT IDusuario, Contraseña FROM usuario WHERE nombre = ? AND correo = ?");
    $queryUser->bind_param("ss", $nombre, $correo);
    $queryUser->execute();
    $queryUser->store_result();
    
    if ($queryUser->num_rows > 0) {
        $queryUser->bind_result($idUsuario, $hash);
        $queryUser->fetch();
    
        if (password_verify($contraseña, $hash)) {
            $_SESSION['usuario_id'] = $idUsuario;
            $_SESSION['nombre'] = $nombre;
            header("Location: Imprenta_Cisneros.php");
            exit();
        } else {
            echo "<p style='color:red;'>Contraseña incorrecta para usuario.</p>";
            exit();
        }
    }
    

    echo "<p style='color:red;'>Usuario no encontrado en ninguna tabla.</p>";
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Imprenta Cisneros</title>
    <link rel="shortcut icon" href="Logo/Recurso-8.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --cisneros-blue: #2271c2;
            --cisneros-magenta: #d6027b;
            --cisneros-yellow: #ffd400;
            --cisneros-orange: #f06e00;
            --cisneros-light-blue: #62b5e5;
            
        }
        
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        
        header {
            width: 100%;
            background: linear-gradient(to right, rgb(246, 215, 143), rgb(250, 244, 157));
            padding: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 300px;
            margin: 0 auto;
        }
        
        .logo-container img {
            width: 100%;
            height: auto;
        }
        
        .login-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 40px auto;
            border-top: 4px solid var(--cisneros-magenta);
        }
        
        .login-title {
            color: var(--cisneros-blue);
            text-align: center;
            margin-bottom: 25px;
            font-weight: bold;
        }
        
        .form-control {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 12px;
        }
        
        .form-control:focus {
            border-color: var(--cisneros-yellow);
            box-shadow: 0 0 0 0.25rem rgba(255, 212, 0, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(to right, var(--cisneros-orange), var(--cisneros-magenta));
            border: none;
            color: white;
            padding: 12px;
            width: 100%;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: linear-gradient(to right, var(--cisneros-magenta), var(--cisneros-orange));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-link a {
            color: var(--cisneros-blue);
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link a:hover {
            color: var(--cisneros-magenta);
        }
        
        .error-message {
            color: var(--cisneros-magenta);
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }
        
        .colored-bar {
            height: 5px;
            margin: 20px 0;
            background: linear-gradient(to right, 
                var(--cisneros-light-blue) 0%, 
                var(--cisneros-light-blue) 20%, 
                var(--cisneros-orange) 20%, 
                var(--cisneros-orange) 40%, 
                var(--cisneros-yellow) 40%, 
                var(--cisneros-yellow) 60%, 
                var(--cisneros-yellow) 60%, 
                var(--cisneros-yellow) 80%, 
                var(--cisneros-magenta) 80%, 
                var(--cisneros-magenta) 100%);
        }
        
        html, body {
            height: 100%;
            margin: 0;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .container {
            flex: 1;
        }
        footer {
            background: linear-gradient(to right, rgba(214, 245, 255, 0.88), rgba(75, 207, 255, 0.85));
            color: #6c757d;
            padding: 20px 40px; /* Más espacio a los lados */
            margin-top: auto;
            height: 100px;
            display: flex;
            align-items: center;
        }
        .footer-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px; /* para que no se extienda demasiado en pantallas grandes */
            margin: 0 auto; /* centra el contenedor */
        }

    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="Logo/Recurso 5.png" alt="Imprenta Cisneros Logo" onerror="this.onerror=null; this.src='/api/placeholder/300/150'; this.alt='Logo de Imprenta Cisneros';">
        </div>
    </header>
    <!--Contenedor-->
    <div class="container">
        <div class="login-container">
            <h2 class="login-title">Iniciar Sesión</h2>
            <div class="colored-bar"></div>
            <!--Formulario-->
            <form method="POST" action="Iniciarsesion.php">
                <div class="mb-3">
                    <input type="text" name="nombre" placeholder="Nombre de usuario" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <input type="email" name="correo" placeholder="Correo electrónico" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <input type="password" name="contraseña" placeholder="Contraseña" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-login">Entrar</button>
                
                <div class="register-link">
                    <p>¿No tienes una cuenta? <a href="Registro.php">Regístrate aquí</a></p>
                </div>
                
                <div class="error-message">
                    <?php echo $mensaje ?? ''; ?>
                </div>
            </form>
        </div>
    </div>
    <!--Pie de pagina-->
    <footer>
        <div class="footer-container">
            <span class="left">Sitio realizado por Huelicatl</span>
            <span class="right">© 2025 IMPRENTA CISNEROS S. DE R.L. DE C.V</span>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
