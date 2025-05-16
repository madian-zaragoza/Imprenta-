<?php
session_start();
// Evita que el navegador guarde la página en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['admin'])) {
    header("Location: Iniciarsesion.php");
    exit();
}

include 'Conexion.php';

// Procesar el cierre de sesión si se confirma
if (isset($_POST['cerrar_sesion']) && $_POST['cerrar_sesion'] == '1') {
    session_destroy();
    header("Location: Iniciarsesion.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración | Imprenta Cisneros</title>
    <link rel="shortcut icon" href="Logo/Recurso-8.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --cisneros-blue: #2271c2;
            --cisneros-magenta: #d6027b;
            --cisneros-yellow: #ffd400;
            --cisneros-orange: #f06e00;
            --cisneros-light-blue: #62b5e5;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: linear-gradient(to right, rgba(214, 245, 255, 0.88), rgba(75, 207, 255, 0.85));
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 60px;
            margin-right: 15px;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .admin-icon {
            background-color: white;
            color: var(--cisneros-blue);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .admin-menu {
            position: relative;
            cursor: pointer;
        }
        
        .admin-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 220px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            padding: 15px;
            z-index: 1000;
            display: none;
            margin-top: 10px;
        }
        
        .admin-dropdown.show {
            display: block;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .admin-dropdown-item {
            display: flex;
            align-items: center;
            padding: 10px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        
        .admin-dropdown-item:hover {
            background-color: #f0f0f0;
        }
        
        .admin-dropdown-item i {
            margin-right: 10px;
            color: var(--cisneros-magenta);
        }
        
        .logout-btn {
            color: #dc3545;
            border-top: 1px solid #eee;
            margin-top: 10px;
            padding-top: 10px;
        }
        
        .logout-btn i {
            color: #dc3545;
        }
        
        .modal-confirm .modal-content {
            padding: 20px;
            border-radius: 10px;
        }
        
        .modal-confirm .modal-header {
            border-bottom: none;
            position: relative;
        }
        
        .modal-confirm h4 {
            color: var(--cisneros-blue);
        }
        
        .modal-confirm .btn-confirm {
            background-color: var(--cisneros-magenta);
            border-color: var(--cisneros-magenta);
        }
        
        .modal-confirm .btn-cancel {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .panel-title {
            color: var(--cisneros-blue);
            font-weight: bold;
            margin-bottom: 25px;
            text-align: center;
            position: relative;
        }
        
        .panel-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--cisneros-magenta), var(--cisneros-orange));
        }
        
        .action-buttons {
            margin-bottom: 30px;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .btn-primary {
            background-color: var(--cisneros-blue);
            border-color: var(--cisneros-blue);
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #1b5c9e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary {
            background-color: var(--cisneros-light-blue);
            border-color: var(--cisneros-light-blue);
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: #4da3d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .search-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table-title {
            color: var(--cisneros-blue);
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--cisneros-yellow);
        }
        
        .table thead {
            background: linear-gradient(to right, var(--cisneros-blue), var(--cisneros-light-blue));
            color: white;
        }
        
        .table th {
            font-weight: 600;
            padding: 12px;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 123, 255, 0.05);
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            margin: 2px;
            border-radius: 30px;
        }
        
        .btn-sm {
            border-radius: 30px;
            padding: 5px 12px;
            transition: all 0.2s ease;
        }
        
        .btn-warning {
            background-color: var(--cisneros-orange);
            border-color: var(--cisneros-orange);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d86000;
            color: white;
        }
        
        .btn-danger {
            background-color: var(--cisneros-magenta);
            border-color: var(--cisneros-magenta);
        }
        
        .btn-danger:hover {
            background-color: #b5026a;
        }
        
        .btn-info {
            background-color: var(--cisneros-light-blue);
            border-color: var(--cisneros-light-blue);
            color: white;
        }
        
        .btn-info:hover {
            background-color: #4da3d4;
            color: white;
        }
        
        .form-control:focus {
            border-color: var(--cisneros-yellow);
            box-shadow: 0 0 0 0.25rem rgba(255, 212, 0, 0.25);
        }
        
        footer {
            background: linear-gradient(to right, rgb(246, 215, 143), rgb(250, 244, 157));
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

        
        .status-available {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-unavailable {
            color: var(--cisneros-magenta);
            font-weight: bold;
        }
        
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #e9ecef;
        }
        
        /* Responsive tweaks */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }
            
            .action-buttons button {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container">
        <div class="logo-container">
            <div class="logo">
                <img src="Logo/Recurso 5.png" alt="Imprenta Cisneros Logo" onerror="this.onerror=null; this.src='/api/placeholder/180/60'; this.alt='Logo de Imprenta Cisneros';">
                <div>
                    <h1 class="mb-0"></h1>
                    <p class="mb-0"></p>
                </div>
            </div>
            <div class="admin-info">
                <div class="admin-menu" id="adminMenu">
                    <div class="admin-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-bold">Administrador <i class="fas fa-chevron-down ms-1"></i></p>
                    </div>
                    <div class="admin-dropdown" id="adminDropdown">
                        <div class="admin-dropdown-item logout-btn" id="logoutBtn">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Modal de confirmación para cerrar sesión -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-confirm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel"><i class="fas fa-question-circle me-2"></i>Confirmar Cierre de Sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cerrar sesión?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST">
                    <input type="hidden" name="cerrar_sesion" value="1">
                    <button type="submit" class="btn btn-danger btn-confirm"><a href="Iniciarsesion.php">Sí, cerrar sesión </a></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container main-content">
    <h1 class="panel-title">Panel de Control</h1>

    <div class="action-buttons text-center">
        <form method="POST">
            <button type="submit" name="ver_productos" class="btn btn-primary m-2">
                <i class="fas fa-box-open me-2"></i>Ver Productos
            </button>
            <button type="submit" name="ver_historial" class="btn btn-secondary m-2">
                <i class="fas fa-history me-2"></i>Ver Historial de Ventas
            </button>
        </form>
    </div>

    <div>
    <?php
if (isset($_POST['ver_productos'])) {
    echo "<div class='search-container'>
            <form method='GET' class='d-flex justify-content-between align-items-center'>
                <div class='input-group'>
                    <span class='input-group-text bg-light'>
                        <i class='fas fa-search'></i>
                    </span>
                    <input type='text' name='busqueda_producto' class='form-control' placeholder='Buscar por tipo de producto' value='" . ($_GET['busqueda_producto'] ?? '') . "' />
                    <button type='submit' class='btn btn-outline-primary'>Buscar</button>
                </div>
                <a href='NuevoProducto.html' class='btn btn-success ms-3'>
                    <i class='fas fa-plus me-2'></i>Agregar Producto
                </a>
            </form>
          </div>";

    $busqueda = $_GET['busqueda_producto'] ?? '';

    // Consulta con JOIN para obtener también el nombre del género
   $sql = "SELECT p.*, GROUP_CONCAT(g.Genero SEPARATOR ', ') AS NombreGenero
        FROM producto p
        LEFT JOIN genero g ON p.IDproducto = g.IDproducto
        WHERE p.Tipo LIKE '%$busqueda%'
        GROUP BY p.IDproducto";

    $result = $conn->query($sql);

    echo "<div class='table-container'>
            <h2 class='table-title'><i class='fas fa-box-open me-2'></i>Lista de Productos</h2>
            <div class='table-responsive'>
                <table class='table table-striped table-bordered'>
                    <thead>
                        <tr>
                            <th><i class='fas fa-tag me-1'></i>Nombre</th>
                            <th><i class='fas fa-info-circle me-1'></i>Tipo</th>
                            <th><i class='fas fa-filter me-1'></i>Género</th>
                            <th><i class='fas fa-dollar-sign me-1'></i>Precio</th>
                            <th><i class='fas fa-ruler me-1'></i>Tamaños</th>
                            <th><i class='fas fa-cubes me-1'></i>Stock</th>
                            <th><i class='fas fa-check-circle me-1'></i>Disponibilidad</th>
                            <th><i class='fas fa-chart-line me-1'></i>Ventas</th>
                            <th><i class='fas fa-image me-1'></i>Imagen</th>
                            <th><i class='fas fa-edit me-1'></i>Editar</th>
                            <th><i class='fas fa-trash me-1'></i>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>";

    while ($row = $result->fetch_assoc()) {
        $idProducto = $row['IDproducto'];

        // Obtener imagen principal
        $sql_img = "SELECT Imagen FROM imagen WHERE IDproducto = $idProducto LIMIT 1";
        $result_img = $conn->query($sql_img);
        $imagen = ($result_img->num_rows > 0) ? $result_img->fetch_assoc()['Imagen'] : 'imagen/default.png';

        echo "<tr>
                <td>{$row['Nombre']}</td>
                <td>{$row['Tipo']}</td>
                <td>{$row['NombreGenero']}</td>
                <td><strong>\${$row['Precio']}</strong></td>
                <td>";

        // Obtener tamaños asociados
        $sql_tamaño = "SELECT Tamaño FROM tamaño WHERE IDproducto = $idProducto";
        $result_tamaño = $conn->query($sql_tamaño);
        if ($result_tamaño->num_rows > 0) {
            while ($tam = $result_tamaño->fetch_assoc()) {
                echo "<span class='badge bg-info text-white me-1'>{$tam['Tamaño']}</span>";
            }
        } else {
            echo "<span class='text-muted'>Sin tamaños</span>";
        }

        echo    "</td>
                <td><span class='badge bg-secondary'>{$row['Stock']}</span></td>
                <td>";
        if ($row['Disponibilidad'] == 1) {
            echo "<span class='status-available'><i class='fas fa-check-circle me-1'></i>Disponible</span>";
        } else {
            echo "<span class='status-unavailable'><i class='fas fa-times-circle me-1'></i>No disponible</span>";
        }
        echo    "</td>
                <td><span class='badge bg-primary'>{$row['Ventas']}</span></td>
                <td><img src='$imagen' class='product-img'></td>
                <td>
                    <a href='EditarProducto.php?id={$row['IDproducto']}' class='btn btn-warning btn-sm'>
                        <i class='fas fa-edit'></i> Editar
                    </a>
                </td>
                <td>
                    <a href='EliminarProducto.php?id={$row['IDproducto']}' class='btn btn-danger btn-sm'>
                        <i class='fas fa-trash'></i> Eliminar
                    </a>
                </td>
              </tr>";
    }

    echo "</tbody></table></div></div>";
}

if (isset($_POST['ver_historial'])) {
    echo "<div class='search-container'>
            <form method='GET' class='d-flex justify-content-between align-items-center'>
                <div class='input-group'>
                    <span class='input-group-text bg-light'>
                        <i class='fas fa-search'></i>
                    </span>
                    <input type='text' name='busqueda_historial' class='form-control' placeholder='Buscar por nombre o usuario' value='".($_GET['busqueda_historial'] ?? '')."'>
                    <button type='submit' class='btn btn-outline-secondary'>Buscar</button>
                </div>
            </form>
          </div>";

    $busqueda = $_GET['busqueda_historial'] ?? '';
    $sql = "SELECT 
                h.IDhistorial,
                p.IDpedido,
                p.IDusuario,
                u.Nombre AS NombreUsuario,
                p.Telefono,
                p.Remitente,
                p.Direccion,
                p.Estado AS EstadoPedido,
                p.Total,
                p.Fecha
            FROM historialventas h
            INNER JOIN pedido p ON h.IDpedido = p.IDpedido
            INNER JOIN usuario u ON p.IDusuario = u.IDusuario
            WHERE u.Nombre LIKE '%$busqueda%'";

    $result = $conn->query($sql);

    echo "<div class='table-container'>
            <h2 class='table-title'><i class='fas fa-history me-2'></i>Historial de Ventas</h2>
            <div class='table-responsive'>
                <table class='table table-striped table-bordered'>
                    <thead>
                        <tr>
                            <th><i class='fas fa-hashtag me-1'></i>Número de Pedido</th>
                            <th><i class='fas fa-user me-1'></i>Usuario</th>
                            <th><i class='fas fa-calendar me-1'></i>Fecha</th>
                            <th><i class='fas fa-info-circle me-1'></i>Estado</th>
                            <th><i class='fas fa-edit me-1'></i>Editar Estado</th>
                            <th><i class='fas fa-cogs me-1'></i>Actualizar Estado</th>
                            <th><i class='fas fa-search-plus me-1'></i>Detalles</th>
                            <th><i class='fas fa-trash me-1'></i>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>";

    while ($row = $result->fetch_assoc()) {
        $idPedido = $row['IDpedido'];
        $idHistorial = $row['IDhistorial'];
        $estado = $row['EstadoPedido'];

        // Colores para los estados
        $estadoClass = match($estado) {
            'Solicitud enviada' => 'bg-warning',
            'Solicitud vista' => 'bg-primary text-white',
            'Empaquetando pedido' => 'bg-info text-dark',
            'Pedido enviado' => 'bg-secondary',
            'Pedido entregado' => 'bg-success text-white',
            default => 'bg-light text-dark'
        };

        echo "<tr>
                <td><strong>{$idPedido}</strong></td>
                <td>{$row['NombreUsuario']}</td>
                <td>{$row['Fecha']}</td>
                <td><span class='badge $estadoClass'>{$estado}</span></td>
                <td>
                    <form method='POST' action='ActualizarEstadoPedido.php'>
                        <input type='hidden' name='id_pedido' value='{$idPedido}'>
                        <select name='nuevo_estado' class='form-select form-select-sm'>
                            <option ".($estado == 'Solicitud enviada' ? 'selected' : '').">Solicitud enviada</option>
                            <option ".($estado == 'Solicitud vista' ? 'selected' : '').">Solicitud vista</option>
                            <option ".($estado == 'Empaquetando pedido' ? 'selected' : '').">Empaquetando pedido</option>
                            <option ".($estado == 'Pedido enviado' ? 'selected' : '').">Pedido enviado</option>
                            <option ".($estado == 'Pedido entregado' ? 'selected' : '').">Pedido entregado</option>
                        </select>
                </td>
                <td>
                        <button type='submit' class='btn btn-success btn-sm'>
                            <i class='fas fa-check'></i> Actualizar
                        </button>
                    </form>
                </td>
                <td>
                    <button 
                      type='button' 
                      class='btn btn-info btn-sm' 
                      data-bs-toggle='modal' 
                      data-bs-target='#detallesModal' 
                      data-idpedido='{$idPedido}'>
                        <i class='fas fa-eye'></i> Ver
                    </button>
                </td>
                <td>
                    <a href='EliminarVenta.php?id={$idHistorial}' class='btn btn-danger btn-sm' onclick='return confirm(\"¿Estás seguro de eliminar esta venta del historial?\")'>
                        <i class='fas fa-trash'></i> Eliminar
                    </a>
                </td>
              </tr>";
    }

    echo "</tbody></table></div></div>";
}
?>
    </div>
</div>

<!-- Modal detalles -->
<div class="modal fade" id="detallesModal" tabindex="-1" aria-labelledby="detallesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detallesModalLabel">Detalles del Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="contenidoDetalles">
        <!-- Aquí se cargan los detalles con AJAX -->
        <div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando...</div>
      </div>
    </div>
  </div>
</div>

<footer>
    <div class="footer-container">
        <span class="left">Sitio realizado por Huelicatl</span>
        <span class="right">© 2025 IMPRENTA CISNEROS S. DE R.L. DE C.V</span>
    </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menú desplegable del administrador
    const adminMenu = document.getElementById('adminMenu');
    const adminDropdown = document.getElementById('adminDropdown');
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
    
    // Toggle del menú desplegable al hacer clic
    adminMenu.addEventListener('click', function(e) {
        e.stopPropagation();
        adminDropdown.classList.toggle('show');
    });
    
    // Cerrar el menú desplegable al hacer clic fuera
    document.addEventListener('click', function() {
        adminDropdown.classList.remove('show');
    });
    
    // Prevent closing dropdown when clicking inside it
    adminDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Mostrar modal de confirmación al hacer clic en cerrar sesión
    logoutBtn.addEventListener('click', function() {
        logoutModal.show();
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var detallesModal = document.getElementById('detallesModal');
    detallesModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var idPedido = button.getAttribute('data-idpedido');
        var modalBody = detallesModal.querySelector('#contenidoDetalles');

        modalBody.innerHTML = '<div class=\"text-center\"><i class=\"fas fa-spinner fa-spin fa-2x\"></i> Cargando...</div>';

        fetch('DetallesHistorial.php?id=' + idPedido)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
            })
            .catch(() => {
                modalBody.innerHTML = '<div class=\"alert alert-danger\">Error al cargar los detalles.</div>';
            });
    });
});
</script>
</body>
</html>