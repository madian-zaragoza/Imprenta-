<?php
include("Conexion.php");

$idPedido = $_GET['id'] ?? 0;
$idPedido = intval($idPedido);

if ($idPedido <= 0) {
    echo "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>ID de pedido inválido.</div>";
    exit;
}

// Obtener datos del pedido
$sqlPedido = "
    SELECT p.IDpedido, u.Nombre AS Usuario, p.Remitente, p.Direccion, p.Telefono, p.Estado, p.Fecha, p.Total
    FROM pedido p
    INNER JOIN usuario u ON p.IDusuario = u.IDusuario
    WHERE p.IDpedido = ?
";
$stmt = $conn->prepare($sqlPedido);
$stmt->bind_param("i", $idPedido);
$stmt->execute();
$resultPedido = $stmt->get_result();

if ($resultPedido->num_rows === 0) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle me-2'></i>Pedido no encontrado.</div>";
    exit;
}

$pedido = $resultPedido->fetch_assoc();

// Obtener productos del pedido con color, tamaño y primera imagen
$sqlProductos = "
    SELECT 
        pd.Cantidad, 
        pd.PrecioUnitario, 
        pr.Nombre AS Producto, 
        c.Color AS Color, 
        t.Tamaño AS Tamaño, 
        im.Imagen AS Imagen
    FROM PedidoDetalle pd
    INNER JOIN Producto pr ON pd.IDproducto = pr.IDproducto
    LEFT JOIN Color c ON pd.IDcolor = c.IDcolor
    LEFT JOIN Tamaño t ON pd.IDtamaño = t.IDtamaño
    LEFT JOIN (
        SELECT i1.IDproducto, i1.Imagen
        FROM Imagen i1
        INNER JOIN (
            SELECT IDproducto, MIN(IDimagen) AS MinIDimagen
            FROM Imagen
            GROUP BY IDproducto
        ) i2 ON i1.IDproducto = i2.IDproducto AND i1.IDimagen = i2.MinIDimagen
    ) im ON im.IDproducto = pr.IDproducto
    WHERE pd.IDpedido = ?
";

$stmtProd = $conn->prepare($sqlProductos);
$stmtProd->bind_param("i", $idPedido);
$stmtProd->execute();
$resultProd = $stmtProd->get_result();

// Definir las variables de colores para consistencia con el panel principal
$cisnerosBlue = "#2271c2";
$cisnerosMagenta = "#d6027b";
$cisnerosYellow = "#ffd400";
$cisnerosOrange = "#f06e00";
$cisnerosLightBlue = "#62b5e5";
?>

<style>
    /* Estilos específicos para los detalles del pedido */
    .detalles-pedido {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .detalles-header {
        background: linear-gradient(to right, <?php echo $cisnerosBlue; ?>, <?php echo $cisnerosLightBlue; ?>);
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .detalles-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .info-item {
        background-color: #f8f9fa;
        border-left: 4px solid <?php echo $cisnerosYellow; ?>;
        padding: 12px 15px;
        border-radius: 0 5px 5px 0;
    }
    
    .info-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    
    .info-value {
        font-size: 1.05rem;
        color: #333;
    }
    
    .productos-title {
        color: <?php echo $cisnerosBlue; ?>;
        font-size: 1.3rem;
        margin: 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid <?php echo $cisnerosYellow; ?>;
        display: flex;
        align-items: center;
    }
    
    .tabla-productos {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 15px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        overflow: hidden;
    }
    
    .tabla-productos thead {
        background: linear-gradient(to right, <?php echo $cisnerosBlue; ?>, <?php echo $cisnerosLightBlue; ?>);
        color: white;
    }
    
    .tabla-productos th {
        padding: 14px 15px;
        font-weight: 600;
        text-align: center;
        position: relative;
        letter-spacing: 0.5px;
    }
    
    .tabla-productos th::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 30%;
        height: 3px;
        background-color: rgba(255, 255, 255, 0.4);
        border-radius: 2px;
    }
    
    .tabla-productos th i {
        display: block;
        font-size: 1.1rem;
        margin-bottom: 5px;
        text-align: center;
    }
    
    .tabla-productos td {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        text-align: center;
        transition: all 0.2s ease;
    }
    
    .tabla-productos tbody tr {
        background-color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .tabla-productos tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        z-index: 1;
        position: relative;
    }
    
    .tabla-productos tbody tr:last-child td {
        border-bottom: none;
    }
    
    .tabla-productos tbody tr:not(:last-child) {
        position: relative;
    }
    
    .tabla-productos tbody tr:not(:last-child)::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 5%;
        width: 90%;
        height: 1px;
        background: linear-gradient(to right, transparent, #e0e0e0, transparent);
    }
    
    .product-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 3px solid #f0f0f0;
        transition: all 0.3s;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }
    
    .product-img:hover {
        transform: scale(1.15);
        border-color: <?php echo $cisnerosYellow; ?>;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }
    
    .color-circle {
        display: inline-block;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        position: relative;
    }
    
    .color-circle::after {
        content: '';
        position: absolute;
        top: -4px;
        left: -4px;
        right: -4px;
        bottom: -4px;
        border-radius: 50%;
        border: 1px solid #ddd;
        opacity: 0.5;
    }
    
    .badge-size {
        background: linear-gradient(135deg, <?php echo $cisnerosBlue; ?> 0%, <?php echo $cisnerosLightBlue; ?> 100%);
        color: white;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        letter-spacing: 0.5px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .price {
        font-weight: 700;
        color: <?php echo $cisnerosMagenta; ?>;
        position: relative;
        display: inline-block;
    }
    
    .price::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 100%;
        height: 2px;
        background: linear-gradient(to right, transparent, <?php echo $cisnerosMagenta; ?>, transparent);
        opacity: 0.6;
    }
    
    .total-row {
        background: linear-gradient(to right, #f8f9fa, #f1f3f5);
        font-weight: 700;
    }
    
    .total-price {
        color: <?php echo $cisnerosMagenta; ?>;
        font-size: 1.3rem;
        font-weight: 800;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        position: relative;
        display: inline-block;
        padding: 0 10px;
    }
    
    .total-price::before, .total-price::after {
        content: '•';
        position: absolute;
        font-size: 1.2rem;
        color: <?php echo $cisnerosYellow; ?>;
        font-weight: bold;
    }
    
    .total-price::before {
        left: -5px;
    }
    
    .total-price::after {
        right: -5px;
    }
    
    .estado-badge {
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .print-btn {
        background-color: <?php echo $cisnerosOrange; ?>;
        border: none;
        color: white;
        padding: 8px 20px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        font-weight: 600;
    }
    
    .print-btn:hover {
        background-color: #d86000;
        transform: translateY(-2px);
    }
    
    .print-btn i {
        margin-right: 8px;
    }
    
    @media (max-width: 768px) {
        .detalles-info {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="detalles-pedido">
    <div class="detalles-header">
        <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Detalles del Pedido #<?= $pedido['IDpedido'] ?></h4>
    </div>
    
    <div class="detalles-info">
        <div class="info-item">
            <div class="info-label"><i class="fas fa-user me-2"></i>Cliente</div>
            <div class="info-value"><?= htmlspecialchars($pedido['Usuario']) ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label"><i class="fas fa-user-tag me-2"></i>Remitente</div>
            <div class="info-value"><?= htmlspecialchars($pedido['Remitente']) ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label"><i class="fas fa-phone me-2"></i>Teléfono</div>
            <div class="info-value"><?= htmlspecialchars($pedido['Telefono']) ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label"><i class="fas fa-calendar-alt me-2"></i>Fecha</div>
            <div class="info-value"><?= htmlspecialchars($pedido['Fecha']) ?></div>
        </div>
    </div>
    
    <div class="info-item mb-4" style="grid-column: 1 / -1;">
        <div class="info-label"><i class="fas fa-map-marker-alt me-2"></i>Dirección de Entrega</div>
        <div class="info-value"><?= htmlspecialchars($pedido['Direccion']) ?></div>
    </div>
    
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="productos-title"><i class="fas fa-shopping-basket me-2"></i>Productos en el Pedido</div>
        <?php
        // Determinar la clase del estado según su valor
        $estadoClass = match($pedido['Estado']) {
            'Solicitud enviada' => 'bg-warning text-dark',
            'Solicitud vista' => 'bg-primary text-white',
            'Empaquetando pedido' => 'bg-info text-dark',
            'Pedido enviado' => 'bg-secondary text-white',
            'Pedido entregado' => 'bg-success text-white',
            default => 'bg-light text-dark'
        };
        ?>
        <span class="estado-badge <?= $estadoClass ?>">
            <?php 
            // Ícono según el estado
            $estadoIcon = match($pedido['Estado']) {
                'Solicitud enviada' => '<i class="fas fa-paper-plane me-1"></i>',
                'Solicitud vista' => '<i class="fas fa-eye me-1"></i>',
                'Empaquetando pedido' => '<i class="fas fa-box me-1"></i>',
                'Pedido enviado' => '<i class="fas fa-truck me-1"></i>',
                'Pedido entregado' => '<i class="fas fa-check-circle me-1"></i>',
                default => '<i class="fas fa-circle me-1"></i>'
            };
            echo $estadoIcon . htmlspecialchars($pedido['Estado']);
            ?>
        </span>
    </div>
    
    <div class="table-responsive">
        <table class="tabla-productos">
            <thead>
                <tr>
                    <th style="width: 100px;"><i class="fas fa-image"></i>Imagen</th>
                    <th><i class="fas fa-tag"></i>Producto</th>
                    <th style="width: 90px;"><i class="fas fa-sort-amount-up"></i>Cantidad</th>
                    <th style="width: 80px;"><i class="fas fa-palette"></i>Color</th>
                    <th style="width: 100px;"><i class="fas fa-ruler"></i>Tamaño</th>
                    <th style="width: 120px;"><i class="fas fa-calculator"></i>Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $totalCalculado = 0;
            while ($prod = $resultProd->fetch_assoc()) : 
                $subtotal = $prod['Cantidad'] * $prod['PrecioUnitario'];
                $totalCalculado += $subtotal;

                $nombreImagen = $prod['Imagen'];

                // Eliminar prefijo "imagenes/" si existe para evitar repetir carpeta
                if (strpos($nombreImagen, 'imagenes/') === 0) {
                    $nombreImagen = substr($nombreImagen, strlen('imagenes/'));
                }

                $rutaImagenServidor = __DIR__ . "/imagenes/" . $nombreImagen;
            ?>
                <tr>
                    <td>
                        <?php 
                        if (!empty($nombreImagen) && file_exists($rutaImagenServidor)) : ?>
                            <img src="imagenes/<?= htmlspecialchars($nombreImagen) ?>" 
                                alt="<?= htmlspecialchars($prod['Producto']) ?>" 
                                class="product-img">
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fas fa-image text-muted" style="font-size: 2rem;"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="fw-semibold"><?= htmlspecialchars($prod['Producto']) ?></td>
                    <td class="text-center fw-semibold"><?= $prod['Cantidad'] ?></td>
                    <td>
                        <?php if (!empty($prod['Color'])): ?>
                            <div class="d-flex justify-content-center">
                                <span class="color-circle" style="background-color: <?= htmlspecialchars($prod['Color']) ?>;"></span>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($prod['Tamaño'])): ?>
                            <span class="badge-size"><?= htmlspecialchars($prod['Tamaño']) ?></span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="price">$<?= number_format($prod['PrecioUnitario'], 2) ?></td>
                    <td class="price">$<?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endwhile; ?>
                <tr class="total-row">
                    <td colspan="5" class="text-end pe-4 fs-5">Total del Pedido:</td>
                    <td colspan="2" class="total-price">$<?= number_format($pedido['Total'] ?? $totalCalculado, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Script para animar elementos en la carga
document.addEventListener('DOMContentLoaded', function() {
    const detallesInfo = document.querySelectorAll('.info-item');
    
    detallesInfo.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 100 * index);
    });
});
</script>