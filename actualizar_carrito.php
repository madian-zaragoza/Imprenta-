<?php
session_start();
include 'Conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$idCarrito = $_POST['idCarrito'] ?? null;
$cantidad = $_POST['cantidad'] ?? null;
$color = $_POST['color'] ?? null;
$tamaño = $_POST['tamaño'] ?? null;

if (!isset($idCarrito, $cantidad)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}


if (!is_numeric($cantidad) || $cantidad < 1) {
    echo json_encode(['success' => false, 'message' => 'Cantidad inválida']);
    exit;
}

// Obtener producto del carrito
$stmt = $conn->prepare("SELECT IDproducto FROM carrito WHERE IDcarrito = ? AND IDusuario = ?");
$stmt->bind_param("ii", $idCarrito, $usuarioId);
$stmt->execute();
$stmt->bind_result($idProducto);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Carrito no encontrado']);
    exit;
}
$stmt->close();

// Buscar duplicados (mismo producto, color, tamaño y distinto ID)
$stmt2 = $conn->prepare("SELECT IDcarrito, Cantidad FROM carrito WHERE IDusuario = ? AND IDproducto = ? AND IDcolor = ? AND IDtamaño = ? AND IDcarrito <> ?");
$stmt2->bind_param("iiiii", $usuarioId, $idProducto, $color, $tamaño, $idCarrito);
$stmt2->execute();
$stmt2->store_result();

if ($stmt2->num_rows > 0) {
    $stmt2->bind_result($idDuplicado, $cantidadDuplicado);
    $stmt2->fetch();

    $nuevaCantidad = $cantidad + $cantidadDuplicado;

    // Actualizar cantidad duplicado
    $stmtUpdate = $conn->prepare("UPDATE carrito SET Cantidad = ? WHERE IDcarrito = ?");
    $stmtUpdate->bind_param("ii", $nuevaCantidad, $idDuplicado);
    $stmtUpdate->execute();

    // Eliminar fila actual (ya sumada)
    $stmtDelete = $conn->prepare("DELETE FROM carrito WHERE IDcarrito = ?");
    $stmtDelete->bind_param("i", $idCarrito);
    $stmtDelete->execute();

    echo json_encode(['success' => true, 'message' => 'Productos unidos']);
    exit;
}
$stmt2->close();

// Si no hay duplicado, actualizar normalmente
$stmtUpdate = $conn->prepare("UPDATE carrito SET Cantidad = ?, IDcolor = NULLIF(?, 0), IDtamaño = NULLIF(?, 0) WHERE IDcarrito = ? AND IDusuario = ?");
$color = $color ?? 0;
$tamaño = $tamaño ?? 0;
$stmtUpdate->bind_param("iiiii", $cantidad, $color, $tamaño, $idCarrito, $usuarioId);

if ($stmtUpdate->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}
?>
