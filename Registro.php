<?php
include 'Conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contrasena = password_hash($_POST['contraseña'], PASSWORD_DEFAULT); // ← corregido
    $telefono = $_POST['teléfono'];
    $direccion = $_POST['dirección'];

    // Verificar si ya existe el correo
    $verificar = $conn->prepare("SELECT IDusuario FROM usuario WHERE correo = ?");
    $verificar->bind_param("s", $correo);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows > 0) {
        echo "<p style='color:red;'>Ese correo ya está registrado.</p>";
    } else {
        $sql = "INSERT INTO usuario (nombre, correo, contraseña, teléfono, dirección)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nombre, $correo, $contrasena, $telefono, $direccion);

        if ($stmt->execute()) {
            // Redirigir si todo salió bien
            header("Location: Imprenta_Cisneros.php");
            exit();
        } else {
            echo "<p style='color:red;'>Error al registrar: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }

    $verificar->close();
    $conn->close();
}
?>





<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario | Imprenta Cisneros</title>
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
        
        html, body {
            height: 100%;
            margin: 0;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .container {
            flex: 1;
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
            width: 60%;
            height: auto;
        }
        
        .register-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 40px auto;
            border-top: 4px solid var(--cisneros-orange);
        }
        
        .register-title {
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
        
        .error {
            color: var(--cisneros-magenta);
            font-size: 0.85rem;
            margin-top: -10px;
            margin-bottom: 10px;
            display: none;
        }
        
        .btn-register {
            background: linear-gradient(to right, var(--cisneros-blue), var(--cisneros-light-blue));
            border: none;
            color: white;
            padding: 12px;
            width: 100%;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            background: linear-gradient(to right, var(--cisneros-light-blue), var(--cisneros-blue));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: var(--cisneros-blue);
            text-decoration: none;
            font-weight: bold;
        }
        
        .login-link a:hover {
            color: var(--cisneros-magenta);
        }
        
        .colored-bar {
            height: 5px;
            margin: 20px 0;
            background: linear-gradient(to right, 
                var(--cisneros-blue) 0%, 
                var(--cisneros-blue) 20%, 
                var(--cisneros-orange) 20%, 
                var(--cisneros-orange) 40%, 
                var(--cisneros-yellow) 40%, 
                var(--cisneros-yellow) 60%, 
                var(--cisneros-light-blue) 60%, 
                var(--cisneros-light-blue) 80%, 
                var(--cisneros-magenta) 80%, 
                var(--cisneros-magenta) 100%);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .error-message {
            color: var(--cisneros-magenta);
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
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
    
    <div class="container">
        <div class="register-container">
            <h2 class="register-title">Formulario de Registro</h2>
            <div class="colored-bar"></div>
            
            <form method="POST" action="Registro.php" id="registerForm">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input type="text" name="nombre" placeholder="Nombre completo" class="form-control" required>
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <input type="email" name="correo" placeholder="Correo electrónico" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <input type="password" name="contraseña" placeholder="Contraseña" class="form-control" required>
                    <span class="error" id="passwordError">La contraseña debe tener entre 6 y 10 caracteres, al menos un número, una letra y un símbolo (@ - _ .)</span>
                </div>
                
                <div class="form-group">
                    <input type="password" name="confirmPassword" placeholder="Confirmar Contraseña" class="form-control" required>
                    <span class="error" id="confirmError">Las contraseñas no coinciden.</span>
                </div>
                
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input type="text" name="teléfono" placeholder="Teléfono" class="form-control">
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <input type="text" name="dirección" placeholder="Dirección" class="form-control">
                    </div>
                </div>
                
                <button type="button" class="btn btn-register" onclick="validateForm()">Registrarse</button>
                
                <div class="login-link">
                    <p>¿Ya tienes una cuenta? <a href="Iniciarsesion.php">Iniciar sesión</a></p>
                </div>
                
                <div class="error-message">
                    <?php echo $mensaje ?? ''; ?>
                </div>
            </form>
        </div>
    </div>
    
    <footer>
        <div class="footer-container">
            <span class="left">Sitio realizado por Huelicatl</span>
            <span class="right">© 2025 IMPRENTA CISNEROS S. DE R.L. DE C.V</span>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            const form = document.getElementById("registerForm");
            const password = form.contraseña.value;
            const confirmPassword = form.confirmPassword.value;

            const passwordError = document.getElementById("passwordError");
            const confirmError = document.getElementById("confirmError");

            // Limpiar errores anteriores
            passwordError.style.display = "none";
            confirmError.style.display = "none";

            // Validar la contraseña
            const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@\-_.])[A-Za-z\d@\-_.]{6,10}$/;
            if (!passwordRegex.test(password)) {
                passwordError.style.display = "block";
                return;
            }

            // Validar que las contraseñas coincidan
            if (password !== confirmPassword) {
                confirmError.style.display = "block";
                return;
            }

            // Si todo está bien, enviar el formulario
            form.submit();
        }
    </script>
</body>
</html>