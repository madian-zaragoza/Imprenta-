<?php
include 'Conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPedido = $_POST['id_pedido'];
    $nuevoEstado = $_POST['nuevo_estado'];

    $sql = "UPDATE pedido SET Estado = ? WHERE IDpedido = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevoEstado, $idPedido);

    if ($stmt->execute()) {
        header("Location: ProcesarPanel.php?mensaje=Estado actualizado correctamente");
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
}
?>
