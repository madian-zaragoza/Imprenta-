<?php
session_start(); // <<--- Esto es fundamental para trabajar con sesiones

include 'Conexion.php'; 

$isLoggedIn = isset($_SESSION['usuario_id']);
if (!$isLoggedIn) {
    die("Debes iniciar sesión para ver tus compras.");
}

// Obtener todos los pedidos
$consulta = "SELECT Pedido.*, Usuario.Nombre AS NombreUsuario 
             FROM Pedido 
             INNER JOIN Usuario ON Pedido.IDusuario = Usuario.IDusuario
             ORDER BY Fecha DESC";
$resultado = mysqli_query($conn, $consulta);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<title>Pedidos  Imprenta Cisneros</title>
<link rel="shortcut icon" href="Logo/Recurso-8.ico" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Imprenta Cisneros</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        /* Variables de colores basados en el logo */
        :root {
            --cisneros-blue: #53b5e6;
            --cisneros-orange: #f06e0c;
            --cisneros-yellow: #ffd429;
            --cisneros-light-yellow: #fff29d;
            --cisneros-magenta: #e81289;
            --cisneros-dark: #222222;
            --cisneros-light: #f8f9fa;
            --cisneros-grey: #6c757d;
        }
        
        /* Estilos generales */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 80px;
            background-color: #fafafa;
            color: var(--cisneros-dark);
            position: relative;
            min-height: 100vh;
            padding-bottom: 120px;
        }

        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 15px;
        }

        h2 {
            color: var(--cisneros-orange);
            text-align: center;
            margin: 30px 0;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, var(--cisneros-magenta), var(--cisneros-orange));
            border-radius: 2px;
        }

        /* ==================== */
        /* Estilos del encabezado */
        /* ==================== */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1050;
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
        }

        .logo-img {
            height: 60px;
            transition: transform 0.3s ease;
        }

        .logo-img:hover {
            transform: scale(1.05);
        }
        
        /* ==================== */
        /* Menú de usuario */
        /* ==================== */
        .user-menu {
            position: relative;
        }

        .user-menu button {
            background-color: white;
            color: var(--cisneros-dark);
            border: 2px solid var(--cisneros-orange);
            border-radius: 50%;
            width: 46px;
            height: 46px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .user-menu button:hover {
            background-color: var(--cisneros-orange);
            color: white;
        }

        .user-menu .fa-heart, 
        .user-menu .fa-shopping-cart {
            color: var(--cisneros-magenta);
            transition: all 0.3s ease;
        }

        .user-menu .fa-heart:hover, 
        .user-menu .fa-shopping-cart:hover {
            transform: scale(1.2);
        }

        .admin-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 10px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border: none;
            width: 220px;
            padding: 10px;
            background-color: white;
            display: none;
            z-index: 1060;
        }

        .admin-dropdown-item {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.2s ease;
        }

        .admin-dropdown-item:hover {
            background-color: #f0f0f0;
        }

        .admin-dropdown-item i {
            color: var(--cisneros-orange);
            width: 24px;
        }

        .admin-dropdown a {
            text-decoration: none;
            color: var(--cisneros-dark);
            display: block;
        }

        /* ==================== */
        /* Estilos de pedidos */
        /* ==================== */
        .pedidos-container {
            max-width: 900px;
            margin: 30px auto;
        }

        .pedido {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 5px solid var(--cisneros-blue);
        }

        .pedido:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }

        .pedido-info {
            flex: 1;
        }

        .pedido-numero {
            color: var(--cisneros-magenta);
            font-size: 1.1rem;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .pedido-fecha {
            color: var(--cisneros-grey);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .pedido-estado {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
            background-color: var(--cisneros-blue);
        }

        .pedido-estado.pendiente {
            background-color: var(--cisneros-yellow);
            color: var(--cisneros-dark);
        }

        .pedido-estado.completado {
            background-color: #4CAF50;
        }

        .pedido-estado.cancelado {
            background-color: #f44336;
        }

        .pedido-acciones {
            display: flex;
            gap: 10px;
        }

        /* Botones */
        .btn-accion {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-detalles {
            background-color: var(--cisneros-light);
            color: var(--cisneros-dark);
            border: 1px solid #ddd;
        }

        .btn-detalles:hover {
            background-color: #e9e9e9;
        }

        .btn-factura {
            background-color: var(--cisneros-orange);
            color: white;
        }

        .btn-factura:hover {
            background-color: #e0650b;
        }

        /* Panel de detalles */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            z-index: 1080;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .detalles-panel {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            z-index: 1090;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translate(-50%, -45%); opacity: 0; }
            to { transform: translate(-50%, -50%); opacity: 1; }
        }

        .btn-cerrar {
            background-color: var(--cisneros-light);
            color: var(--cisneros-dark);
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            display: block;
            margin-left: auto;
        }

        .btn-cerrar:hover {
            background-color: #e0e0e0;
        }

        /* Empty state */
        .no-pedidos {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .no-pedidos i {
            font-size: 60px;
            color: var(--cisneros-grey);
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .no-pedidos h3 {
            color: var(--cisneros-dark);
            margin-bottom: 10px;
        }

        .no-pedidos p {
            color: var(--cisneros-grey);
            margin-bottom: 20px;
        }
        
        /* Footer */
        footer {
            background: linear-gradient(to right, rgb(246, 215, 143), rgb(250, 244, 157));
            color: #6c757d;
            padding: 20px 40px;
            height: 100px;
            display: flex;
            align-items: center;
            position: absolute;
            bottom: 0;
            width: 100%;
            left: 0;
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .pedido {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .pedido-acciones {
                margin-top: 15px;
                width: 100%;
                justify-content: space-between;
            }
            
            .footer-container {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

<header id="header1" class="header px-4 py-2">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Logo -->
            <div>
                <a href="Imprenta Cisneros.php" class="logo">
                    <img src="Logo/Recurso 5.png" alt="Imprenta Cisneros Logo" class="logo-img">   
                </a>
            </div>
            
            <!-- User Menu -->
            <div class="user-menu d-flex align-items-center">
                <?php if ($isLoggedIn): ?>
                <a href="favoritos.php" class="me-3" title="Favoritos">
                    <i class="fas fa-heart fa-lg"></i>
                </a>
                <a href="carrito.php" class="me-3" title="Carrito">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                </a>
                <?php endif; ?>
                
                <div class="position-relative">
                    <button id="userToggle" class="btn">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </button>
                    <div id="userMenu" class="admin-dropdown">
                        <?php if ($isLoggedIn): ?>
                            <div class="admin-dropdown-item"><i class="fas fa-user"></i><?php echo htmlspecialchars($_SESSION['nombre']); ?></div>
                            <div class="admin-dropdown-item"><a href="Pedidos.php"><i class="fas fa-box"></i> Pedidos</a></div>
                            <div class="admin-dropdown-item"><a href="Cerrar.php"><i class="fas fa-sign-out-alt"></i>Cerrar sesión</a></div>
                        <?php else: ?>
                            <div class="admin-dropdown-item"><a href="Iniciarsesion.php"><i class="fas fa-sign-in-alt"></i>Iniciar sesión</a></div>
                            <div class="admin-dropdown-item"><a href="Registro.php"><i class="fas fa-user-plus"></i>Registrarse</a></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container">
    <h2>Mis Pedidos</h2>
    
    <div class="pedidos-container">
        <?php 
        if (mysqli_num_rows($resultado) > 0) {
            while ($pedido = mysqli_fetch_assoc($resultado)) { 
                // Determinar clase para el estado
                $estadoClase = 'pendiente';
                if (strtolower($pedido['Estado']) === 'completado') {
                    $estadoClase = 'completado';
                } else if (strtolower($pedido['Estado']) === 'cancelado') {
                    $estadoClase = 'cancelado';
                }
        ?>
            <div class="pedido">
                <div class="pedido-info">
                    <div class="pedido-numero">Pedido #<?= $pedido['IDpedido'] ?></div>
                    <div class="pedido-fecha">Fecha: <?= date('d/m/Y', strtotime($pedido['Fecha'])) ?></div>
                    <span class="pedido-estado <?= $estadoClase ?>"><?= $pedido['Estado'] ?></span>
                </div>
                <div class="pedido-acciones">
                    <button class="btn-accion btn-detalles" onclick="verDetalles(<?= $pedido['IDpedido'] ?>)">
                        <i class="fas fa-search"></i> Ver detalles
                    </button>
                    <a class="btn-accion btn-factura" href="factura.php?id=<?= $pedido['IDpedido'] ?>">
                        <i class="fas fa-file-invoice"></i> Ver factura
                    </a>
                </div>
            </div>
        <?php 
            }
        } else {
        ?>
            <div class="no-pedidos">
                <i class="fas fa-shopping-bag"></i>
                <h3>No tienes pedidos</h3>
                <p>Aún no has realizado ningún pedido en nuestra tienda.</p>
                <a href="Imprenta Cisneros.php" class="btn-accion btn-factura">
                    <i class="fas fa-shopping-cart"></i> Ir a comprar
                </a>
            </div>
        <?php
        }
        ?>
    </div>
</div>

<!-- Panel emergente para detalles -->
<div id="overlay" class="overlay" onclick="cerrarPanel()"></div>
<div id="detallesPanel" class="detalles-panel"></div>

<footer>
    <div class="footer-container">
        <span class="left">Sitio realizado por Huelicatl</span>
        <span class="right">© 2025 IMPRENTA CISNEROS S. DE R.L. DE C.V</span>
    </div>
</footer>

<script>
    function verDetalles(id) {
        fetch('obtener_detalles_pedido.php?id=' + id)
            .then(response => response.text())
            .then(data => {
                document.getElementById('detallesPanel').innerHTML = data;
                document.getElementById('detallesPanel').style.display = 'block';
                document.getElementById('overlay').style.display = 'block';
                document.body.style.overflow = 'hidden'; // Evitar scroll
            });
    }

    function cerrarPanel() {
        document.getElementById('detallesPanel').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
        document.body.style.overflow = 'auto'; // Restaurar scroll
    }
    
    // Mostrar/ocultar menú de usuario
    document.getElementById('userToggle').addEventListener('click', function(event) {
        event.stopPropagation();
        const menu = document.getElementById('userMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
    
    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', function(event) {
        const userMenu = document.getElementById('userMenu');
        const userToggle = document.getElementById('userToggle');
        
        if (userMenu.style.display === 'block' && 
            !userMenu.contains(event.target) && 
            !userToggle.contains(event.target)) {
            userMenu.style.display = 'none';
        }
    });
</script>

</body>
</html>