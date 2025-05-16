<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    echo "Error: usuario no autenticado.";
    exit;
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
    FROM Carrito ca
    JOIN Producto p ON ca.IDproducto = p.IDproducto
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
$sqlPedido = "INSERT INTO Pedido (IDusuario, Remitente, Telefono, Direccion, Total, Estado) VALUES (?, ?, ?, ?, ?, ?)";
$stmtPedido = $conn->prepare($sqlPedido);
$stmtPedido->bind_param("isssds", $IDusuario, $remitente, $telefono, $direccion, $total, $estado);

if (!$stmtPedido->execute()) {
    echo "Error al crear el pedido.";
    exit;
}

$IDpedido = $stmtPedido->insert_id;

// **Insertar registro en HistorialVentas:**
$sqlHistorial = "INSERT INTO historialventas (IDpedido) VALUES (?)";
$stmtHistorial = $conn->prepare($sqlHistorial);
$stmtHistorial->bind_param("i", $IDpedido);
$stmtHistorial->execute();

// Luego continúa con detalles y vaciar carrito...

// Insertar detalles del pedido con precio unitario correcto
$sqlDetalle = "INSERT INTO PedidoDetalle (IDpedido, IDproducto, IDcolor, IDtamaño, Cantidad, PrecioUnitario) 
               VALUES (?, ?, ?, ?, ?, ?)";
$stmtDetalle = $conn->prepare($sqlDetalle);

foreach ($productos as $producto) {
    $stmtDetalle->bind_param(
        "iiiiid",
        $IDpedido,
        $producto['IDproducto'],
        $producto['IDcolor'],
        $producto['IDtamaño'],
        $producto['Cantidad'],
        $producto['Precio']
    );
    $stmtDetalle->execute();
}

// Vaciar carrito
$sqlVaciar = "DELETE FROM Carrito WHERE IDusuario = ?";
$stmtVaciar = $conn->prepare($sqlVaciar);
$stmtVaciar->bind_param("i", $IDusuario);
$stmtVaciar->execute();

echo "<script>
    alert('¡Pedido realizado con éxito! Se generará tu factura.');
    window.location.href = 'factura.php?id={$IDpedido}';
</script>";
