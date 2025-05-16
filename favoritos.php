<?php
session_start();
include 'Conexion.php';

$isLoggedIn = isset($_SESSION['usuario_id']);
if (!$isLoggedIn) {
    die("Debes iniciar sesión para ver tus favoritos.");
}
$idUsuario = $_SESSION['usuario_id'];

$sql = "SELECT p.IDproducto, p.Nombre, p.Tipo, p.Precio AS PrecioActual, i.Imagen, ld.Precio AS PrecioGuardado
        FROM ListaDeseos ld
        JOIN Producto p ON ld.IDproducto = p.IDproducto
        LEFT JOIN (
            SELECT IDproducto, MIN(Imagen) AS Imagen
            FROM Imagen
            GROUP BY IDproducto
        ) i ON i.IDproducto = p.IDproducto
        WHERE ld.IDusuario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title> Guardados | Imprenta Cisneros</title>
    <link rel="shortcut icon" href="Logo/Recurso-8.ico" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        /* Estilos de la página de favoritos */
        /* ==================== */
        .page-title {
            text-align: center;
            margin: 30px 0;
            font-weight: 700;
            color: var(--cisneros-dark);
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--cisneros-orange), var(--cisneros-magenta));
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .title-container {
            text-align: center;
            margin-bottom: 40px;
        }

        .lista-favoritos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            padding: 0 20px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .producto {
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            max-width: 240px;
            margin: 0 auto;
            width: 100%;
        }

        .producto:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        .producto-img-container {
            width: 100%;
            height: 160px;
            overflow: hidden;
            position: relative;
            background-color: #f8f8f8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .producto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .producto:hover img {
            transform: scale(1.05);
        }

        .producto-info {
            padding: 12px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .producto-nombre {
            font-size: 1.05rem;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--cisneros-dark);
        }

        .producto-tipo {
            color: var(--cisneros-grey);
            font-size: 0.85rem;
            margin-bottom: 8px;
        }

        .producto-precios {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 12px;
        }

        .precio-guardado, .precio-actual {
            padding: 4px 8px;
            border-radius: 16px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .precio-guardado {
            background-color: var(--cisneros-light-yellow);
            color: var(--cisneros-dark);
        }

        .precio-actual {
            background-color: var(--cisneros-blue);
            color: white;
        }

        .producto-acciones {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
        }

        .btn-wishlist {
            background-color: white;
            color: var(--cisneros-magenta);
            border: 1px solid var(--cisneros-magenta);
            border-radius: 6px;
            padding: 6px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
            width: 100%;
            justify-content: center;
        }

        .btn-wishlist:hover {
            background-color: var(--cisneros-magenta);
            color: white;
        }

        .btn-wishlist i {
            font-size: 0.9rem;
        }

        .empty-favorites {
            text-align: center;
            padding: 40px 20px;
            border-radius: 12px;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        .empty-favorites i {
            font-size: 3rem;
            color: var(--cisneros-grey);
            margin-bottom: 20px;
        }

        .empty-favorites h3 {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .empty-favorites p {
            color: var(--cisneros-grey);
            margin-bottom: 20px;
        }

        .btn-explore {
            background-color: var(--cisneros-orange);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-explore:hover {
            background-color: #d55e09;
            transform: translateY(-2px);
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
            .lista-favoritos {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 15px;
            }
            
            .producto {
                max-width: 200px;
            }
            
            .producto-img-container {
                height: 140px;
            }
            
            .producto-nombre {
                font-size: 0.95rem;
            }
            
            .footer-container {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 10px;
            }
        }

        @media (max-width: 576px) {
            .lista-favoritos {
                grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
            }
            
            .producto-precios {
                flex-direction: column;
                gap: 5px;
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
    <div class="title-container">
        <h1 class="page-title">Mis Productos Favoritos</h1>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="lista-favoritos">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="producto">
                    <a href="Detalles.php?IDproducto=<?= $row['IDproducto'] ?>" class="text-decoration-none">
                        <div class="producto-img-container">
                            <img src="<?= htmlspecialchars($row['Imagen']) ?>" alt="<?= htmlspecialchars($row['Nombre']) ?>">
                        </div>
                        <div class="producto-info">
                            <h3 class="producto-nombre"><?= htmlspecialchars($row['Nombre']) ?></h3>
                            <p class="producto-tipo"><i class="fas fa-tag me-2"></i><?= htmlspecialchars($row['Tipo']) ?></p>
                            <div class="producto-precios">
                                <span class="precio-guardado">
                                    <i class="fas fa-bookmark me-1"></i> Precio guardado: $<?= $row['PrecioGuardado'] ?>
                                </span>
                                <span class="precio-actual">
                                    <i class="fas fa-tags me-1"></i> Precio actual: $<?= $row['PrecioActual'] ?>
                                </span>
                            </div>
                        </div>
                    </a>
                    <div class="producto-acciones">
                        <button type="button" class="btn-wishlist eliminar" data-id="<?= $row['IDproducto'] ?>" 
                        data-precio="<?= $row['PrecioGuardado'] ?>">
                        <i class="fas fa-heart-broken"></i> Eliminar de favoritos
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-favorites">
            <i class="far fa-heart"></i>
            <h3>No tienes productos en tu lista de favoritos</h3>
            <p>Explora nuestra tienda y agrega productos a tus favoritos</p>
            <a href="Imprenta Cisneros.php" class="btn-explore">Explorar productos</a>
        </div>
    <?php endif; ?>
</div>

<footer>
    <div class="footer-container">
        <span class="left">Sitio realizado por Huelicatl</span>
        <span class="right">© 2025 IMPRENTA CISNEROS S. DE R.L. DE C.V</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.btn-wishlist.eliminar').forEach(btn => {
        btn.addEventListener('click', function () {
            const productoId = this.dataset.id;
            const precio = this.dataset.precio;

            fetch('toggle_deseo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `productoId=${productoId}&precio=${precio}`
            })
            .then(res => {
                if (res.status === 401) {
                    alert("Debes iniciar sesión para modificar la lista de deseos.");
                    return;
                }
                return res.json();
            })
            .then(data => {
                if (data && data.accion === 'eliminado') {
                    // Opcional: quitar el producto del DOM
                    this.closest('.producto').remove();
                    
                    // Si no quedan más productos, mostrar mensaje
                    if (document.querySelectorAll('.producto').length === 0) {
                        document.querySelector('.lista-favoritos').innerHTML = `
                            <div class="empty-favorites">
                                <i class="far fa-heart"></i>
                                <h3>No tienes productos en tu lista de favoritos</h3>
                                <p>Explora nuestra tienda y agrega productos a tus favoritos</p>
                                <a href="Imprenta Cisneros.php" class="btn-explore">Explorar productos</a>
                            </div>
                        `;
                    }
                }
            })
            .catch(err => {
                console.error("Error al eliminar de la lista de deseos:", err);
            });
        });
    });

    // Mostrar/ocultar menú de usuario
    document.getElementById('userToggle').addEventListener('click', function() {
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