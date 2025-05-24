<?php
include("Conexion.php");

$idPedido = $_GET['id'] ?? 0;
$idPedido = intval($idPedido);

// Colores corporativos
$cisnerosBlue = "#2271c2";
$cisnerosMagenta = "#d6027b";
$cisnerosYellow = "#ffd400";
$cisnerosOrange = "#f06e00";
$cisnerosLightBlue = "#62b5e5";

if ($idPedido <= 0) {
    echo "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>ID de pedido inválido.</div>";
    exit;
}

// Obtener datos del pedido
$sqlPedido = "
    SELECT p.idpedido, u.nombre AS usuario, p.remitente, p.direccion, p.telefono, p.estado, p.fecha, p.total
    FROM pedido p
    INNER JOIN usuario u ON p.idusuario = u.idusuario
    WHERE p.idpedido = ?
";
$stmtPedido = $conn->prepare($sqlPedido);
$stmtPedido->bind_param("i", $idPedido);
$stmtPedido->execute();
$resultPedido = $stmtPedido->get_result();

if ($resultPedido->num_rows === 0) {
    echo "<div class='alert alert-warning'><i class='fas fa-info-circle me-2'></i>Pedido no encontrado.</div>";
    exit;
}

$pedido = $resultPedido->fetch_assoc();

// Obtener productos del pedido con color, tamaño y primera imagen
$sqlProductos = "
    SELECT 
        pd.Cantidad AS cantidad, 
        pd.PrecioUnitario AS preciounitario, 
        pr.Nombre AS producto, 
        pd.Color AS color, 
        pd.Tamaño AS tamaño, 
        (
            SELECT Imagen 
            FROM imagen 
            WHERE IDproducto = pr.IDproducto 
            LIMIT 1
        ) AS imagen
    FROM pedidodetalle pd
    INNER JOIN producto pr ON pd.IDproducto = pr.IDproducto
    WHERE pd.IDpedido = ?
";


$stmtProd = $conn->prepare($sqlProductos);
$stmtProd->bind_param("i", $idPedido);
$stmtProd->execute();
$resultProd = $stmtProd->get_result();

// Clases para los estados de pedido
function obtenerClaseEstado($estado) {
    switch(strtolower($estado)) {
        case 'entregado':
            return 'bg-success';
        case 'en proceso':
            return 'bg-primary';
        case 'pendiente':
            return 'bg-warning';
        case 'cancelado':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
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
        width: 110%;
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
        width: 40%;
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
        <h4><i class="fas fa-shopping-bag me-2"></i>Detalles del Pedido #<?php echo $pedido['idpedido']; ?></h4>
        <span class="estado-badge <?php echo obtenerClaseEstado($pedido['estado']); ?> text-white">
            <?php echo $pedido['estado']; ?>
        </span>
      
    </div>
    
    <div class="detalles-info">
        <div class="info-item">
            <div class="info-label">Usuario</div>
            <div class="info-value"><i class="fas fa-user me-2"></i><?php echo $pedido['usuario']; ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Remitente</div>
            <div class="info-value"><i class="fas fa-id-card me-2"></i><?php echo $pedido['remitente']; ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Teléfono</div>
            <div class="info-value"><i class="fas fa-phone me-2"></i><?php echo $pedido['telefono']; ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Dirección</div>
            <div class="info-value"><i class="fas fa-map-marker-alt me-2"></i><?php echo $pedido['direccion']; ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Fecha</div>
            <div class="info-value"><i class="fas fa-calendar-alt me-2"></i><?php echo $pedido['fecha']; ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total</div>
            <div class="info-value"><i class="fas fa-dollar-sign me-2"></i><?php echo $pedido['total']; ?></div>
        </div>
    </div>
    
    <?php if ($resultProd->num_rows > 0): ?>
        <div class="productos-title">
            <i class="fas fa-boxes me-2"></i>Productos del Pedido
        </div>
        
        <div class="table-responsive">
            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th><i class="fas fa-image"></i>Imagen</th>
                        <th><i class="fas fa-tag"></i>Producto</th>
                        <th><i class="fas fa-palette"></i>Color</th>
                        <th><i class="fas fa-ruler"></i>Tamaño</th>
                        <th><i class="fas fa-sort-amount-up"></i>Cantidad</th>
                        <th><i class="fas fa-tag"></i>Precio Unit.</th>
                        <th><i class="fas fa-calculator"></i>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalPedido = 0;
                    while ($row = $resultProd->fetch_assoc()): 
                        $subtotal = $row['cantidad'] * $row['preciounitario'];
                        $totalPedido += $subtotal;
                        $colorHex = '#CCCCCC'; // Color por defecto
                        // Aquí podrías tener una función para obtener el color hexadecimal según el nombre
                    ?>
                        <tr>
                            <td>
                                <?php if ($row['imagen']): ?>
                                    <img src="<?php echo $row['imagen']; ?>" class="product-img" alt="<?php echo $row['producto']; ?>">
                                <?php else: ?>
                                    <div class="text-center text-muted"><i class="fas fa-image fa-2x"></i><br>Sin imagen</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['producto']; ?></td>
           <td>
    <?php if (!empty($row['color'])): ?>
        <div class="d-flex justify-content-center">
            <span class="color-circle" style="background-color: <?= htmlspecialchars($row['color']) ?>;"></span>
        </div>
    <?php else: ?>
        <span class="text-muted">-</span>
    <?php endif; ?>
</td>


                    <td>
    <?php if (!empty($row['tamaño'])): ?>
        <span class="badge-size"><?= htmlspecialchars($row['tamaño']) ?></span>
    <?php else: ?>
        <span class="text-muted">-</span>
    <?php endif; ?>
</td>

                            <td><?php echo $row['cantidad']; ?></td>
                            <td><span class="price">$<?php echo number_format($row['preciounitario'], 2); ?></span></td>
                            <td><span class="price">$<?php echo number_format($subtotal, 2); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                    <tr class="total-row">
                        <td colspan="5"></td>
                        <td>Total:</td>
                        <td><span class="total-price">$<?php echo number_format($pedido['total'], 2); ?></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary">
            <i class="fas fa-info-circle me-2"></i>No se encontraron productos en este pedido.
        </div>
    <?php endif; ?>
</div>