<?php
session_start();
include 'Conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('Debes iniciar sesión para añadir al carrito.'); window.history.back();</script>";
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$idProducto = $_POST['idProducto'] ?? null;
$idColor = $_POST['idColor'] ?? null;
$idTamaño = $_POST['idTamaño'] ?? null;
$cantidad = $_POST['cantidad'] ?? 1;

// Validación preliminar
if (!$idProducto || $cantidad < 1) {
    echo "<script>alert('Faltan datos o cantidad inválida.'); window.history.back();</script>";
    exit;
}

// Verificar si el producto tiene colores
$sqlColorCheck = "SELECT COUNT(*) FROM color WHERE IDproducto = ?";
$stmtColorCheck = $conn->prepare($sqlColorCheck);
$stmtColorCheck->bind_param("i", $idProducto);
$stmtColorCheck->execute();
$stmtColorCheck->bind_result($totalColores);
$stmtColorCheck->fetch();
$stmtColorCheck->close();

// Verificar si el producto tiene tamaños
$sqlTamañoCheck = "SELECT COUNT(*) FROM tamaño WHERE IDproducto = ?";
$stmtTamañoCheck = $conn->prepare($sqlTamañoCheck);
$stmtTamañoCheck->bind_param("i", $idProducto);
$stmtTamañoCheck->execute();
$stmtTamañoCheck->bind_result($totalTamaños);
$stmtTamañoCheck->fetch();
$stmtTamañoCheck->close();

// Validar color y tamaño solo si son requeridos
if (($totalColores > 0 && !$idColor) || ($totalTamaños > 0 && !$idTamaño)) {
    echo "<script>alert('Faltan datos requeridos para este producto.'); window.history.back();</script>";
    exit;
}

// Si no son requeridos, asignar NULL
$idColor = ($totalColores > 0) ? $idColor : null;
$idTamaño = ($totalTamaños > 0) ? $idTamaño : null;

// Verificar si ya existe el producto con las mismas características
$sql = "SELECT IDcarrito FROM carrito WHERE IDproducto = ? AND IDusuario = ? AND (IDcolor <=> ?) AND (IDtamaño <=> ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $idProducto, $usuarioId, $idColor, $idTamaño);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "<script>alert('Este producto ya está en tu carrito con las mismas opciones.'); window.history.back();</script>";
} else {
    $insert = $conn->prepare("INSERT INTO carrito (IDproducto, IDusuario, IDcolor, IDtamaño, Cantidad) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("iiiii", $idProducto, $usuarioId, $idColor, $idTamaño, $cantidad);

    if ($insert->execute()) {
        echo "<script>alert('Producto añadido al carrito exitosamente.'); window.location.href='Detalles.php?IDproducto=$idProducto';</script>";
    } else {
        echo "<script>alert('Error al añadir al carrito.'); window.history.back();</script>";
    }
}
?>
