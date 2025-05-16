<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: InicioSesion.php");
    exit();
}

include 'Conexion.php';

// Verifica si se recibió el ID por la URL
if (isset($_GET['id'])) {
    $idProducto = $_GET['id'];

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


    // 4. Eliminar producto
    $sql = "DELETE FROM producto WHERE IDproducto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idProducto);

    if ($stmt->execute()) {
        echo "<script>alert('Producto, colores, imágenes y tamaños eliminados correctamente'); window.location.href = 'ProcesarPanel.php';</script>";
    } else {
        echo "<script>alert('Error al eliminar el producto'); window.location.href = 'ProcesarPanel.php';</script>";
    }

    $stmt->close();
} else {
    // Si no viene ID, regresa al panel
    header("Location: ProcesarPanel.php");
    exit();
}

$conn->close();
?>
