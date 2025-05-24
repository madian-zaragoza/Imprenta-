<?php
session_start();
include 'Conexion.php';

$isLoggedIn = isset($_SESSION['usuario_id']);
if (!$isLoggedIn) {
    die("Debes iniciar sesión para ver tus compras.");
}
$idUsuario = $_SESSION['usuario_id'];

$stmt = $conn->prepare("
    SELECT 
        ca.IDcarrito,
        p.IDproducto,
        p.Nombre,
        p.Precio,
        ca.Cantidad,
        ca.IDcolor,
        ca.IDtamaño,
        co.Color AS NombreColor,
        t.Tamaño AS NombreTamaño,
        (SELECT Imagen FROM imagen WHERE IDproducto = p.IDproducto LIMIT 1) AS Imagen
    FROM carrito ca
    JOIN producto p ON ca.IDproducto = p.IDproducto
    LEFT JOIN color co ON ca.IDcolor = co.IDcolor
    LEFT JOIN tamaño t ON ca.IDtamaño = t.IDtamaño
    WHERE ca.IDusuario = ?
");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$carritoData = [];
while ($row = $result->fetch_assoc()) {
    $carritoData[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito | Imprenta Cisneros</title>
    <link rel="shortcut icon" href="Logo/Recurso-8.ico" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        /* Estilos del contenido principal */
        /* ==================== */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--cisneros-dark);
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        .page-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--cisneros-orange), var(--cisneros-magenta));
            border-radius: 2px;
        }

        /* ==================== */
        /* Estilos del carrito */
        /* ==================== */
        .carrito-container {
            margin-bottom: 40px;
        }

        .carrito-item {
            background-color: white;
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 20px;
            display: flex;
            gap: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .carrito-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .carrito-item-image {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }

        .carrito-item-details {
            flex: 1;
        }

        .carrito-item-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--cisneros-dark);
        }

        .carrito-item-price {
            font-size: 1.1rem;
            color: var(--cisneros-grey);
            margin-bottom: 8px;
        }

        .carrito-item-subtotal {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--cisneros-dark);
            margin-bottom: 15px;
        }

        .carrito-item-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .carrito-control-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--cisneros-dark);
        }

        .carrito-control-group {
            margin-bottom: 15px;
        }

        .cantidad-input {
            width: 80px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }

        .cantidad-input:focus {
            border-color: var(--cisneros-blue);
            outline: none;
        }

        .tamaño-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            background-color: white;
            transition: border-color 0.2s ease;
        }

        .tamaño-select:focus {
            border-color: var(--cisneros-blue);
            outline: none;
        }

        /* Color options */
        .color-options {
            display: flex;
            gap: 10px;
            margin: 10px 0;
            flex-wrap: wrap;
        }

        .color-radio {
            display: inline-block;
            position: relative;
            cursor: pointer;
        }

        .color-radio input[type="radio"] {
            display: none;
        }

        .color-radio span {
            display: block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .color-radio input[type="radio"]:checked + span {
            border: 2px solid var(--cisneros-dark);
            transform: scale(1.1);
        }

        .color-radio span:hover {
            transform: scale(1.1);
            box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
        }

        .eliminar-btn {
            background-color: white;
            color: #dc3545;
            border: 1px solid #dc3545;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
        }

        .eliminar-btn:hover {
            background-color: #dc3545;
            color: white;
        }

        /* ==================== */
        /* Resumen del carrito */
        /* ==================== */
        .cart-summary {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .cart-total {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--cisneros-dark);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-total-label {
            font-size: 1.5rem;
            color: var(--cisneros-grey);
        }

        .cart-total-amount {
            font-size: 2rem;
            color: var(--cisneros-magenta);
        }

        .comprar-btn {
            background: linear-gradient(to right, var(--cisneros-orange), var(--cisneros-magenta));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 15px 25px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .comprar-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .empty-cart-icon {
            font-size: 4rem;
            color: var(--cisneros-grey);
            margin-bottom: 20px;
        }

        .empty-cart-message {
            font-size: 1.5rem;
            color: var(--cisneros-dark);
            margin-bottom: 30px;
        }

        .continue-shopping-btn {
            background-color: var(--cisneros-blue);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .continue-shopping-btn:hover {
            background-color: #3d9bd4;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Modal de compra */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--cisneros-dark);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .modal-content .form-label {
            font-weight: 500;
            color: var(--cisneros-dark);
            margin-bottom: 8px;
            display: block;
        }
        
        .modal-content .form-control {
            width: 100%;
            margin-bottom: 20px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .modal-content .form-control:focus {
            border-color: var(--cisneros-blue);
            box-shadow: 0 0 0 3px rgba(83, 181, 230, 0.2);
            outline: none;
        }
        
        .modal-content textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .modal-btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-confirm {
            background: linear-gradient(to right, var(--cisneros-orange), var(--cisneros-magenta));
            color: white;
            border: none;
            flex-grow: 1;
        }
        
        .btn-confirm:hover {
            box-shadow: 0 5px 15px rgba(240, 110, 12, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background-color: white;
            color: var(--cisneros-grey);
            border: 1px solid #ddd;
            margin-right: 10px;
        }
        
        .btn-cancel:hover {
            background-color: #f8f9fa;
            border-color: #c8c8c8;
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

        /* Responsive styles */
        @media (max-width: 768px) {
            .carrito-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .carrito-item-image {
                width: 180px;
                height: 180px;
                margin-bottom: 15px;
            }

            .carrito-item-actions {
                grid-template-columns: 1fr;
            }

            .cart-total {
                flex-direction: column;
                gap: 10px;
            }
            
            .modal-content {
                width: 95%;
                padding: 20px;
            }
            
            .modal-footer {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-cancel {
                margin-right: 0;
                margin-bottom: 10px;
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
                <a href="Imprenta_Cisneros.php" class="logo">
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

<div class="main-container">
    <h1 class="page-title">Mi Carrito</h1>

    <div class="carrito-container">
        <?php if (count($carritoData) > 0): ?>
            <?php foreach ($carritoData as $row):
                $subtotal = $row['Precio'] * $row['Cantidad'];
                $total += $subtotal;
            ?>
            <div class="carrito-item" data-idcarrito="<?= $row['IDcarrito'] ?>">
                <img class="carrito-item-image" src="<?= htmlspecialchars($row['Imagen']) ?>" alt="<?= htmlspecialchars($row['Nombre']) ?>">
                <div class="carrito-item-details">
                    <h3 class="carrito-item-title"><?= htmlspecialchars($row['Nombre']) ?></h3>
                    <p class="carrito-item-price">Precio unitario: $<?= number_format($row['Precio'], 2) ?></p>
                    <p class="carrito-item-subtotal">Subtotal: $<span class="subtotal"><?= number_format($subtotal, 2) ?></span></p>
                    
                    <div class="carrito-item-actions">
                        <div class="carrito-control-group">
                            <label class="carrito-control-label">Cantidad:</label>
                            <input class="actualizar cantidad-input" type="number" name="cantidad" min="1" value="<?= $row['Cantidad'] ?>">
                        </div>

                        <div class="carrito-control-group">
                            <label class="carrito-control-label">Color:</label>
                            <div class="color-options" data-carrito="<?= $row['IDcarrito'] ?>">
                            <?php
                            $colores = $conn->prepare("SELECT IDcolor, Color FROM color WHERE IDproducto = ?");
                            $colores->bind_param("i", $row['IDproducto']);
                            $colores->execute();
                            $resColores = $colores->get_result();
                            while ($color = $resColores->fetch_assoc()) {
                                $selected = ($color['IDcolor'] == $row['IDcolor']) ? "checked" : "";
                                $hexColor = htmlspecialchars($color['Color']);
                                echo "
                                <label class='color-radio'>
                                    <input type='radio' class='actualizar' name='color_{$row['IDcarrito']}' value='{$color['IDcolor']}' $selected>
                                    <span style='background-color: $hexColor;' title='$hexColor'></span>
                                </label>";
                            }
                            ?>
                            </div>
                        </div>

                        <div class="carrito-control-group">
                            <label class="carrito-control-label">Tamaño:</label>
                            <select class="actualizar tamaño-select" name="tamaño">
                                <?php
                                $tamanos = $conn->prepare("SELECT IDtamaño, Tamaño FROM tamaño WHERE IDproducto = ?");
                                $tamanos->bind_param("i", $row['IDproducto']);
                                $tamanos->execute();
                                $resTamanos = $tamanos->get_result();
                                while ($tam = $resTamanos->fetch_assoc()) {
                                    $selected = ($tam['IDtamaño'] == $row['IDtamaño']) ? "selected" : "";
                                    echo "<option value='{$tam['IDtamaño']}' $selected>{$tam['Tamaño']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <button class="eliminar-btn">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="cart-summary">
                <div class="cart-total">
                    <span class="cart-total-label">Total:</span>
                    <span class="cart-total-amount">$<span id="total"><?= number_format($total, 2) ?></span></span>
                </div>
                
                <button class="comprar-btn" onclick="abrirModal()">
                    <i class="fas fa-shopping-bag"></i> Crear Factura
                </button>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 class="empty-cart-message">Tu carrito está vacío</h2>
                <a href="Imprenta_Cisneros.php" class="continue-shopping-btn">
                    <i class="fas fa-arrow-left"></i> Seguir con el pedido
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Compra -->
<div class="modal" id="modalCompra">
    <div class="modal-content">
        <h3 class="modal-title">Datos para el pedido</h3>
        <form action="procesar_compra.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Remitente:</label>
                <input type="text" name="remitente" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Teléfono:</label>
                <input type="text" name="telefono" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Dirección:</label>
                <textarea name="direccion" class="form-control" required></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="modal-btn btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="modal-btn btn-confirm">Confirmar pedido</button>
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

<script>
function abrirModal() {
    document.getElementById('modalCompra').style.display = 'flex';
}
function cerrarModal() {
    document.getElementById('modalCompra').style.display = 'none';
}
document.querySelectorAll('.color-options').forEach(container => {
    const radios = container.querySelectorAll('input[type="radio"]');
    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            // Disparar el evento change en el input.radio para actualizar
            radio.dispatchEvent(new Event('change'));
        });
    });
});

// Actualizar carrito al cambiar cantidad, tamaño o color (radio)
document.querySelectorAll('.carrito-item').forEach(item => {
    const idCarrito = item.dataset.idcarrito;
    function actualizarCarrito(data) {
        fetch('actualizar_carrito.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        })
        .then(res => res.json())
        .then(resp => {
            if(resp.success) {
                location.reload();
            } else {
                alert(resp.message || 'Error actualizando carrito');
            }
        })
        .catch(() => alert('Error en la comunicación con el servidor'));
    }

    item.querySelectorAll('.actualizar').forEach(input => {
        input.addEventListener('change', () => {
            const cantidad = item.querySelector('input[name="cantidad"]').value;

            // Obtener color seleccionado del radio button con name "color_IDcarrito"
            const colorInput = item.querySelector(`input[name="color_${idCarrito}"]:checked`);
            const color = colorInput ? colorInput.value : '';

            const tamano = item.querySelector('select[name="tamaño"]').value;
            actualizarCarrito({ idCarrito, cantidad, color, tamaño: tamano });
        });
    });

    item.querySelector('.eliminar-btn').addEventListener('click', () => {

        if(confirm('¿Seguro que deseas eliminar este producto del carrito?')) {
            fetch('eliminar_carrito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ idCarrito })
            })
            .then(res => res.json())
            .then(resp => {
                if(resp.success) {
                    item.remove();
                    location.reload();
                } else {
                    alert('Error al eliminar');
                }
            })
            .catch(() => alert('Error en la comunicación con el servidor'));
        }
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

// Scripts que ya tenías: actualizar cantidad/color/tamaño, eliminar productos, menú usuario, etc.
</script>

</body>
</html>


