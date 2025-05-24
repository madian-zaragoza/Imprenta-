<?php
session_start();
include("Conexion.php");

$isLoggedIn = isset($_SESSION['usuario_id']);
if (!$isLoggedIn) {
    die("Debes iniciar sesión para ver tus compras.");
}

$IDusuario = $_SESSION['usuario_id'];
$remitente = $_POST['remitente'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';

// Validación básica
if (empty($remitente) || empty($telefono) || empty($direccion)) {
    echo "Por favor, completa todos los campos.";
    exit;
}

// Obtener productos del carrito con el precio unitario desde Producto
$sqlCarrito = "
    SELECT ca.*, p.Precio
    FROM carrito ca
    JOIN producto p ON ca.IDproducto = p.IDproducto
    WHERE ca.IDusuario = ?
";
$stmt = $conn->prepare($sqlCarrito);
$stmt->bind_param("i", $IDusuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "El carrito está vacío.";
    exit;
}

$total = 0;
$productos = [];

while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila;
    $total += $fila['Precio'] * $fila['Cantidad'];
}

$total = floatval($total);
$estado = "Solicitud enviada";

// Insertar en Pedido con estado
$sqlPedido = "INSERT INTO pedido (IDusuario, Remitente, Telefono, Direccion, Total, Estado) VALUES (?, ?, ?, ?, ?, ?)";
$stmtPedido = $conn->prepare($sqlPedido);
$stmtPedido->bind_param("isssds", $IDusuario, $remitente, $telefono, $direccion, $total, $estado);

if (!$stmtPedido->execute()) {
    echo "Error al crear el pedido.";
    exit;
}

$IDpedido = $stmtPedido->insert_id;

// Insertar registro en historialventas
$sqlHistorial = "INSERT INTO historialventas (IDpedido) VALUES (?)";
$stmtHistorial = $conn->prepare($sqlHistorial);
$stmtHistorial->bind_param("i", $IDpedido);
$stmtHistorial->execute();

// Preparar consulta para obtener nombre del color por IDcolor
$sqlColorNombre = "SELECT Color FROM color WHERE IDcolor = ?";
$stmtColor = $conn->prepare($sqlColorNombre);

// Preparar consulta para obtener nombre del tamaño por IDtamaño
$sqlTamañoNombre = "SELECT Tamaño FROM tamaño WHERE IDtamaño = ?";
$stmtTamaño = $conn->prepare($sqlTamañoNombre);

// Insertar detalles del pedido (guardando nombres reales de color y tamaño)
$sqlDetalle = "INSERT INTO pedidodetalle (IDpedido, IDproducto, Color, Tamaño, Cantidad, PrecioUnitario) 
               VALUES (?, ?, ?, ?, ?, ?)";
$stmtDetalle = $conn->prepare($sqlDetalle);

foreach ($productos as $producto) {
    // Obtener nombre del color
    $colorNombre = "";
    if ($producto['IDcolor']) {
        $stmtColor->bind_param("i", $producto['IDcolor']);
        $stmtColor->execute();
        $stmtColor->bind_result($colorNombre);
        $stmtColor->fetch();
        $stmtColor->reset();
    }

    // Obtener nombre del tamaño
    $tamañoNombre = "";
    if ($producto['IDtamaño']) {
        $stmtTamaño->bind_param("i", $producto['IDtamaño']);
        $stmtTamaño->execute();
        $stmtTamaño->bind_result($tamañoNombre);
        $stmtTamaño->fetch();
        $stmtTamaño->reset();
    }

    $stmtDetalle->bind_param(
        "iissid",
        $IDpedido,
        $producto['IDproducto'],
        $colorNombre,
        $tamañoNombre,
        $producto['Cantidad'],
        $producto['Precio']
    );
    $stmtDetalle->execute();
}

// Vaciar carrito
$sqlVaciar = "DELETE FROM carrito WHERE IDusuario = ?";
$stmtVaciar = $conn->prepare($sqlVaciar);
$stmtVaciar->bind_param("i", $IDusuario);
$stmtVaciar->execute();

echo "<script>
    alert('¡Pedido realizado con éxito! Se generará tu factura.');
    window.location.href = 'factura.php?id={$IDpedido}';
</script>";
