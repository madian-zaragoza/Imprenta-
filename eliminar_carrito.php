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

if (!$idCarrito) {
    echo json_encode(['success' => false, 'message' => 'ID no recibido']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM carrito WHERE IDcarrito = ? AND IDusuario = ?");
$stmt->bind_param("ii", $idCarrito, $usuarioId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
}
