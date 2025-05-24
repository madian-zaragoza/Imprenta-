<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: InicioSesion.php");
    exit();
}

include 'Conexion.php';

if (isset($_GET['id'])) {
    $idProducto = $_GET['id'];

    // 0.1 Eliminar registros relacionados en carrito
    $sqlCarrito = "DELETE FROM carrito WHERE IDproducto = ?";
    $stmtCarrito = $conn->prepare($sqlCarrito);
    $stmtCarrito->bind_param("i", $idProducto);
    $stmtCarrito->execute();
    $stmtCarrito->close();

    // 0.2 Eliminar registros relacionados en pedidodetalle
    $sqlDetalle = "DELETE FROM pedidodetalle WHERE IDproducto = ?";
    $stmtDetalle = $conn->prepare($sqlDetalle);
    $stmtDetalle->bind_param("i", $idProducto);
    $stmtDetalle->execute();
    $stmtDetalle->close();

    // 0.25 Eliminar de historialventas los pedidos que ya no tienen detalles
    $sqlHistorial = "
        DELETE FROM historialventas
        WHERE IDpedido IN (
            SELECT p.IDpedido FROM pedido p
            LEFT JOIN pedidodetalle pd ON p.IDpedido = pd.IDpedido
            WHERE pd.IDpedido IS NULL
        )";
    $conn->query($sqlHistorial);

    // 0.3 Eliminar pedidos vacíos (que ya no tienen detalles)
    $sqlDeletePedidosVacios = "
        DELETE FROM pedido
        WHERE IDpedido NOT IN (
            SELECT DISTINCT IDpedido FROM pedidodetalle
        )";
    $conn->query($sqlDeletePedidosVacios);

    // 0.4 Actualizar totales de los pedidos que aún tienen productos
    $sqlUpdateTotales = "
        UPDATE pedido p
        SET p.total = (
            SELECT IFNULL(SUM(pd.cantidad * pd.preciounitario), 0)
            FROM pedidodetalle pd
            WHERE pd.IDpedido = p.IDpedido
        )
        WHERE p.IDpedido IN (
            SELECT DISTINCT IDpedido FROM pedidodetalle
        )";
    $conn->query($sqlUpdateTotales);

    // 0.5 Eliminar registros en lista de deseos
    $sqlDeseos = "DELETE FROM listadeseos WHERE IDproducto = ?";
    $stmtDeseos = $conn->prepare($sqlDeseos);
    $stmtDeseos->bind_param("i", $idProducto);
    $stmtDeseos->execute();
    $stmtDeseos->close();

    // 1. Eliminar colores relacionados
    $sqlColor = "DELETE FROM color WHERE IDproducto = ?";
    $stmtColor = $conn->prepare($sqlColor);
    $stmtColor->bind_param("i", $idProducto);
    $stmtColor->execute();
    $stmtColor->close();

    // 2. Eliminar imágenes relacionadas
    $sqlImg = "DELETE FROM imagen WHERE IDproducto = ?";
    $stmtImg = $conn->prepare($sqlImg);
    $stmtImg->bind_param("i", $idProducto);
    $stmtImg->execute();
    $stmtImg->close();

    // 3. Eliminar tamaños relacionados
    $sqlTam = "DELETE FROM tamaño WHERE IDproducto = ?";
    $stmtTam = $conn->prepare($sqlTam);
    $stmtTam->bind_param("i", $idProducto);
    $stmtTam->execute();
    $stmtTam->close();

    // 3.5 Eliminar géneros relacionados
    $sqlGen = "DELETE FROM genero WHERE IDproducto = ?";
    $stmtGen = $conn->prepare($sqlGen);
    $stmtGen->bind_param("i", $idProducto);
    $stmtGen->execute();
    $stmtGen->close();

    // 4. Finalmente, eliminar el producto
    $sql = "DELETE FROM producto WHERE IDproducto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idProducto);

    if ($stmt->execute()) {
        echo "<script>alert('Producto y todos sus datos relacionados eliminados correctamente'); window.location.href = 'ProcesarPanel.php';</script>";
    } else {
        echo "<script>alert('Error al eliminar el producto'); window.location.href = 'ProcesarPanel.php';</script>";
    }

    $stmt->close();
} else {
    header("Location: ProcesarPanel.php");
    exit();
}

$conn->close();
?>
