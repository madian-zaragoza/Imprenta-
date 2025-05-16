<?php
include 'Conexion.php';

$id = $_GET['id'] ?? 0;

$consulta = "SELECT Pedido.*, Usuario.Nombre AS NombreUsuario 
             FROM Pedido 
             INNER JOIN Usuario ON Pedido.IDusuario = Usuario.IDusuario
             WHERE Pedido.IDpedido = $id";
$resultado = mysqli_query($conn, $consulta);
$pedido = mysqli_fetch_assoc($resultado);

// Detalles de productos
$productosQuery = "SELECT Producto.IDproducto, Producto.Nombre, PedidoDetalle.Cantidad, PedidoDetalle.PrecioUnitario 
                   FROM PedidoDetalle
                   INNER JOIN Producto ON PedidoDetalle.IDproducto = Producto.IDproducto
                   WHERE PedidoDetalle.IDpedido = $id";
$productos = mysqli_query($conn, $productosQuery);

?>
<button class="btn-cerrar" onclick="cerrarPanel()">
    <i class="fas fa-times"></i> 
</button>
<div class="detalles-header">
    <h3>Detalles del Pedido #<?= $pedido['IDpedido'] ?></h3>
    <div class="estado-badge <?= strtolower($pedido['Estado']) ?>"><?= $pedido['Estado'] ?></div>
</div>

<div class="detalles-grid">
    <div class="detalles-seccion cliente-info">
        <h4><i class="fas fa-user-circle"></i> Información del Cliente</h4>
        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span class="info-value"><?= $pedido['NombreUsuario'] ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Remitente:</span>
            <span class="info-value"><?= $pedido['Remitente'] ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Teléfono:</span>
            <span class="info-value"><?= $pedido['Telefono'] ?></span>
        </div>
    </div>

    <div class="detalles-seccion pedido-info">
        <h4><i class="fas fa-clipboard-list"></i> Detalles de Entrega</h4>
        <div class="info-row">
            <span class="info-label">Fecha:</span>
            <span class="info-value"><?= date('d/m/Y H:i', strtotime($pedido['Fecha'])) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Dirección:</span>
            <span class="info-value"><?= $pedido['Direccion'] ?></span>
        </div>
    </div>
</div>

<div class="productos-seccion">
    <h4><i class="fas fa-box-open"></i> Productos</h4>
    
    <div class="productos-lista">
        <?php 
        $total = 0;
        while ($prod = mysqli_fetch_assoc($productos)) {
            // Consulta para obtener la primera imagen de este producto
            $idProducto = $prod['IDproducto'];
            $imgQuery = "SELECT Imagen FROM Imagen WHERE IDproducto = $idProducto ORDER BY IDimagen ASC LIMIT 1";
            $imgResult = mysqli_query($conn, $imgQuery);
            $imgRow = mysqli_fetch_assoc($imgResult);
            $imgSrc = $imgRow ? $imgRow['Imagen'] : 'img/no-image.png'; // imagen por defecto si no hay
            
            $subtotal = $prod['Cantidad'] * $prod['PrecioUnitario'];
            $total += $subtotal;
        ?>
            <div class="producto-item">
                <div class="producto-imagen">
                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($prod['Nombre']) ?>">
                </div>
                <div class="producto-detalles">
                    <div class="producto-nombre"><?= htmlspecialchars($prod['Nombre']) ?></div>
                    <div class="producto-meta">
                        <span class="cantidad">Cantidad: <?= $prod['Cantidad'] ?></span>
                        <span class="precio">$<?= number_format($prod['PrecioUnitario'], 2) ?> c/u</span>
                    </div>
                </div>
                <div class="producto-precio">
                    $<?= number_format($subtotal, 2) ?>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <div class="resumen-totales">
        <div class="total-line">
            <span>Total:</span>
            <span class="total-precio">$<?= number_format($pedido['Total'], 2) ?></span>
        </div>
    </div>
</div>



<style>
    .detalles-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .detalles-header h3 {
        color: #222;
        font-size: 1.6rem;
        margin: 0;
    }
    
    .estado-badge {
        padding: 6px 15px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
        color: white;
        background-color: #53b5e6;
    }
    
    .estado-badge.pendiente {
        background-color: #ffd429;
        color: #222;
    }
    
    .estado-badge.completado {
        background-color: #4CAF50;
    }
    
    .estado-badge.cancelado {
        background-color: #f44336;
    }
    
    .detalles-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .detalles-seccion {
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 20px;
    }
    
    .detalles-seccion h4 {
        color: #f06e0c;
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-row {
        display: flex;
        margin-bottom: 10px;
    }
    
    .info-label {
        font-weight: 600;
        width: 90px;
        color: #6c757d;
    }
    
    .info-value {
        flex: 1;
    }
    
    .productos-seccion {
        margin-top: 30px;
    }
    
    .productos-seccion h4 {
        color: #e81289;
        font-size: 1.2rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .productos-lista {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .producto-item {
        display: flex;
        align-items: center;
        padding: 15px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .producto-imagen {
        width: 80px;
        height: 80px;
        margin-right: 15px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }
    
    .producto-imagen img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .producto-detalles {
        flex: 1;
    }
    
    .producto-nombre {
        font-weight: 600;
        font-size: 1.05rem;
        margin-bottom: 5px;
        color: #222;
    }
    
    .producto-meta {
        display: flex;
        gap: 15px;
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .producto-precio {
        font-weight: 600;
        font-size: 1.1rem;
        color: #e81289;
    }
    
    .resumen-totales {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px dashed #ddd;
    }
    
    .total-line {
        display: flex;
        justify-content: space-between;
        font-size: 1.2rem;
        font-weight: 600;
        color: #222;
    }
    
    .total-precio {
        color: #e81289;
    }
    
    .btn-cerrar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        background-color: rgba(255, 212, 41, 0.94);
        color: #222;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 5px;
        margin-bottom: 20px;
        margin-left: auto;
    }
    
    .btn-cerrar:hover {
        background-color:rgba(255, 212, 41, 0.8);
    }
    
    @media (max-width: 768px) {
        .detalles-grid {
            grid-template-columns: 1fr;
        }
        
        .detalles-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .estado-badge {
            align-self: flex-start;
        }
        
        .producto-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .producto-imagen {
            margin-bottom: 10px;
            margin-right: 0;
        }
        
        .producto-meta {
            flex-direction: column;
            gap: 5px;
        }
        
        .producto-precio {
            margin-top: 10px;
            align-self: flex-end;
        }
    }
</style>