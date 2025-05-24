<?php
session_start();
include 'Conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$productoId = $_POST['productoId'];
$precio = $_POST['precio'];

// Verifica si el producto ya está en la lista de deseos
$stmt = $conn->prepare("SELECT IDdeseos FROM listadeseos WHERE IDproducto = ? AND IDusuario = ?");
$stmt->bind_param("ii", $productoId, $usuarioId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Ya existe: eliminarlo
    $delete = $conn->prepare("DELETE FROM listadeseos WHERE IDproducto = ? AND IDusuario = ?");
    $delete->bind_param("ii", $productoId, $usuarioId);
    $delete->execute();
    echo json_encode(["accion" => "eliminado"]);
} else {
    // No existe: agregarlo
    $insert = $conn->prepare("INSERT INTO listadeseos (IDproducto, IDusuario, Precio) VALUES (?, ?, ?)");
    $insert->bind_param("iid", $productoId, $usuarioId, $precio);
    $insert->execute();
    echo json_encode(["accion" => "agregado"]);
}

//Hola