
<?php
session_start();
include 'Conexion.php';
$isLoggedIn = isset($_SESSION['usuario_id']);

$idproducto = $_GET['IDproducto'] ?? null;

if (!$idproducto) {
    echo "Producto no especificado.";
    exit();
}

// Obtener datos del producto
$sql = "SELECT * FROM producto WHERE IDproducto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();
$stmt->close();

if (!$producto) {
    echo "Producto no encontrado.";
    exit();
}

// Obtener imágenes
$imagenes = [];
$sqlImg = "SELECT Imagen FROM imagen WHERE IDproducto = ?";
$stmt = $conn->prepare($sqlImg);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$resImg = $stmt->get_result();
while ($row = $resImg->fetch_assoc()) {
    $imagenes[] = $row['Imagen'];
}
$stmt->close();

// Obtener colores (ID y nombre)
$colores = [];
$sqlColor = "SELECT IDcolor, Color FROM color WHERE IDproducto = ?";
$stmt = $conn->prepare($sqlColor);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$resColor = $stmt->get_result();
while ($row = $resColor->fetch_assoc()) {
    $colores[] = $row;
}
$stmt->close();

// Obtener tamaños (ID y nombre)
$tamanos = [];
$sqlTam = "SELECT IDtamaño, Tamaño FROM tamaño WHERE IDproducto = ?";
$stmt = $conn->prepare($sqlTam);
$stmt->bind_param("i", $idproducto);
$stmt->execute();
$resTam = $stmt->get_result();
while ($row = $resTam->fetch_assoc()) {
    $tamanos[] = $row;
}
$stmt->close();

// Verificar si el producto ya está en la lista de deseos
$activo = '';
if ($isLoggedIn) {
    $idUsuario = $_SESSION['usuario_id'];
    $consulta = $conn->query("SELECT 1 FROM ListaDeseos WHERE IDproducto = $idproducto AND IDusuario = $idUsuario");
    if ($consulta->num_rows > 0) {
        $activo = 'activo';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($producto['Nombre']); ?> | Imprenta Cisneros</title>
    <link rel="shortcut icon" href="Logo/Recurso-8.ico" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        /* Estilos de la página de producto */
        /* ==================== */
        
        /* Contenedor principal del producto */
        .product-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        /* Galería de imágenes */
        .thumbnails-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        
        .thumbnail:hover {
            transform: translateY(-3px);
        }
        
        .active-thumb {
            border: 2px solid var(--cisneros-magenta);
            box-shadow: 0 0 8px rgba(232, 18, 137, 0.4);
        }
        
        .main-image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 400px;
            background-color: #f9f9f9;
            border-radius: 12px;
            overflow: hidden;
        }
        
        #mainImage {
            max-height: 380px;
            max-width: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .main-image-container:hover #mainImage {
            transform: scale(1.05);
        }

        /* Detalles del producto */
        .product-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--cisneros-dark);
            margin-bottom: 5px;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--cisneros-magenta);
            margin-bottom: 20px;
        }
        
        .option-label {
            font-weight: 600;
            margin-top: 15px;
            margin-bottom: 8px;
            color: var(--cisneros-dark);
            font-size: 1.1rem;
        }
        
        /* Selector de colores */
        .color-options {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .color-circle {
            display: inline-block;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #e0e0e0;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .color-circle:hover {
            transform: scale(1.1);
        }
        
        .color-circle.selected {
            border: 3px solid var(--cisneros-dark);
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
        }
        
        .color-circle.selected:after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            text-shadow: 0 0 2px rgba(0, 0, 0, 0.8);
            font-weight: bold;
        }
        
        /* Selector de tamaños */
        .size-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 25px;
        }
        
        .size-btn {
            border: 1px solid #ccc;
            background-color: white;
            color: var(--cisneros-dark);
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .size-btn:hover {
            border-color: var(--cisneros-blue);
            color: var(--cisneros-blue);
            transform: translateY(-2px);
        }
        
        .size-btn.active {
            background-color: var(--cisneros-blue);
            color: white;
            border-color: var(--cisneros-blue);
            font-weight: 600;
        }
        
        /* Selector de cantidad */
        .quantity-selector {
            display: flex;
            align-items: center;
            max-width: 150px;
            margin-bottom: 25px;
        }
        
        .quantity-label {
            font-weight: 600;
            margin-right: 10px;
            color: var(--cisneros-dark);
        }
        
        .quantity-input {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 12px;
            width: 80px;
            font-weight: 600;
            text-align: center;
            transition: all 0.2s ease;
        }
        
        .quantity-input:focus {
            border-color: var(--cisneros-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(83, 181, 230, 0.2);
        }
        
        /* Botones de acción */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .btn-cart {
            background-color: var(--cisneros-blue);
            color: white;
            font-weight: 600;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(83, 181, 230, 0.3);
        }
        
        .btn-cart:hover {
            background-color: #4295c7;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(83, 181, 230, 0.4);
        }
        
        .btn-wishlist {
            background-color: white;
            color: var(--cisneros-dark);
            font-weight: 600;
            padding: 12px 25px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-wishlist:hover {
            border-color: var(--cisneros-magenta);
            color: var(--cisneros-magenta);
        }
        
        .btn-wishlist.activo {
            background-color: rgba(232, 18, 137, 0.1);
            border-color: var(--cisneros-magenta);
        }
        
        .btn-wishlist.activo i {
            color: var(--cisneros-magenta);
        }
        
        /* Descripción del producto */
        .product-description-section {
            margin-top: 40px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 30px;
        }
        
        .description-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--cisneros-dark);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--cisneros-yellow);
        }
        
        .product-description {
            font-size: 1.05rem;
            line-height: 1.7;
            color: #444;
        }
        
        /* Footer */
        footer {
            background: linear-gradient(to right, rgb(246, 215, 143), rgb(250, 244, 157));
            color: #6c757d;
            padding: 20px 40px;
            margin-top: 50px;
            height: 100px;
            display: flex;
            align-items: center;
        }
    
        .footer-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Script Styles */
        #userMenu {
            display: none;
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
    <!-- Contenedor del producto -->
    <div class="product-container">
        <div class="row">
            <!-- Thumbnails -->
            <div class="col-md-1">
                <div class="thumbnails-container">
                    <?php foreach ($imagenes as $index => $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" class="thumbnail <?php echo $index === 0 ? 'active-thumb' : ''; ?>" onclick="changeMainImage(this)" alt="Miniatura <?php echo $index + 1; ?>">
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Imagen principal -->
            <div class="col-md-5">
                <div class="main-image-container">
                    <img id="mainImage" src="<?php echo htmlspecialchars($imagenes[0] ?? ''); ?>" alt="<?php echo htmlspecialchars($producto['Nombre']); ?>">
                </div>
            </div>

            <!-- Información del producto -->
            <div class="col-md-6">
                <h1 class="product-title"><?php echo htmlspecialchars($producto['Nombre']); ?></h1>
                <p class="product-price">$MXN <?php echo number_format($producto['Precio'], 2); ?></p>

                <p class="option-label">Color:</p>
                <div class="color-options" id="colorContainer">
                    <?php foreach ($colores as $color): ?>
                        <span class="color-circle" style="background-color: <?php echo htmlspecialchars($color['Color']); ?>;" data-id="<?php echo $color['IDcolor']; ?>" data-color="<?php echo htmlspecialchars($color['Color']); ?>"></span>
                    <?php endforeach; ?>
                </div>

                <p class="option-label">Talla:</p>
                <div class="size-options">
                    <?php foreach ($tamanos as $tam): ?>
                        <button type="button" class="size-btn" data-id="<?php echo $tam['IDtamaño']; ?>" onclick="selectSize(this)"><?php echo htmlspecialchars($tam['Tamaño']); ?></button>
                    <?php endforeach; ?>
                </div>

                <form method="post" action="Añadir.php" onsubmit="return validarSeleccion()">
                    <input type="hidden" name="idProducto" value="<?php echo $idproducto; ?>">
                    <input type="hidden" name="idColor" id="colorSeleccionado">
                    <input type="hidden" name="idTamaño" id="tamañoSeleccionado">

                    <div class="quantity-selector">
                        <label for="cantidad" class="quantity-label">Cantidad:</label>
                        <input type="number" name="cantidad" id="cantidad" class="quantity-input" min="1" value="1">
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn-cart">
                            <i class="fas fa-shopping-cart"></i> Añadir al carrito
                        </button>

                        <button type="button" class="btn-wishlist favorito <?php echo $activo; ?>" id="btnGuardar" data-id="<?php echo $producto['IDproducto']; ?>" data-precio="<?php echo $producto['Precio']; ?>">
                            <i class="fas fa-heart"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Descripción del producto -->
    <div class="product-description-section">
        <h2 class="description-title">Descripción del producto</h2>
        <div class="product-description">
            <?php echo nl2br(htmlspecialchars($producto['Descripción'])); ?>
        </div>
    </div>
</div>

<footer>
    <div class="footer-container">
        <span class="left">Sitio realizado por Huelicatl</span>
        <span class="right">© 2025 IMPRENTA CISNEROS S. DE R.L. DE C.V</span>
    </div>
</footer>

<script>
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

    function changeMainImage(thumb) {
        const mainImg = document.getElementById('mainImage');
        mainImg.src = thumb.src;

        document.querySelectorAll('.thumbnail').forEach(img => img.classList.remove('active-thumb'));
        thumb.classList.add('active-thumb');
    }

    function selectSize(btn) {
        document.getElementById('tamañoSeleccionado').value = btn.getAttribute('data-id');
        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    document.querySelectorAll('.color-circle').forEach(span => {
        span.addEventListener('click', function () {
            document.getElementById('colorSeleccionado').value = this.getAttribute('data-id');
            document.querySelectorAll('.color-circle').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    function validarSeleccion() {
    const colorContainer = document.getElementById('colorContainer');
    const color = document.getElementById('colorSeleccionado').value;
    const tamaño = document.getElementById('tamañoSeleccionado').value;
    const cantidad = document.getElementById('cantidad').value;

    const hayColores = colorContainer && colorContainer.querySelectorAll('.color-circle').length > 0;
    const hayTallas = document.querySelectorAll('.size-btn').length > 0;

    if (hayColores && !color) {
        alert('Por favor selecciona un color.');
        return false;
    }
    if (hayTallas && !tamaño) {
        alert('Por favor selecciona un tamaño.');
        return false;
    }
    if (!cantidad || cantidad < 1) {
        alert('Por favor ingresa una cantidad válida.');
        return false;
    }
    return true;
}


    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.addEventListener('click', function () {
        const productoId = btnGuardar.dataset.id;
        const precio = btnGuardar.dataset.precio;

        fetch('toggle_deseo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `productoId=${productoId}&precio=${precio}`
        })
        .then(res => {
            if (res.status === 401) {
                alert("Debes iniciar sesión para guardar en la lista de deseos.");
                return;
            }
            return res.json();
        })
        .then(data => {
            if (data && data.accion === 'agregado') {
                btnGuardar.classList.add('activo');
            } else if (data && data.accion === 'eliminado') {
                btnGuardar.classList.remove('activo');
            }
        })
        .catch(err => {
            console.error("Error al guardar en la lista de deseos:", err);
        });
    });
</script>

</body>
</html>