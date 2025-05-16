<?php
include 'Conexion.php';

if (isset($_GET['id'])) {
    $idHistorial = $_GET['id'];

    $sql = "DELETE FROM historialventas WHERE IDhistorial = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idHistorial);

    if ($stmt->execute()) {
        header("Location: ProcesarPanel.php?mensaje=Venta eliminada del historial");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
