<?php
session_start();

// 1. Verificamos si el administrador ha iniciado sesión
if (!isset($_SESSION['admin'])) {
    header("Location: InicioSesion.php");
    exit();
}

// 2. Conectamos a la base de datos
include 'Conexion.php'; // Asegúrate de que $conn esté definido ahí

// 3. Recibimos los datos del formulario
$nombre = $_POST['nombre'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$precio = $_POST['precio'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$ventas = $_POST['ventas'] ?? 0;
$disponible = $_POST['disponibilidad'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

// 4. Insertamos el producto en la tabla `producto`
$sql = "INSERT INTO producto (Nombre, Tipo, Precio, Stock, Ventas, Disponibilidad, Descripción) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparando SQL para producto: " . $conn->error);
}

$stmt->bind_param("ssdiiss", $nombre, $tipo, $precio, $stock, $ventas, $disponible, $descripcion);
$stmt->execute();
$idProducto = $stmt->insert_id;
$stmt->close();

// 5. Insertamos los tamaños seleccionados
if (!empty($_POST['Tamaño']) && is_array($_POST['Tamaño'])) {
    $stmtTamaño = $conn->prepare("INSERT INTO tamaño (IDproducto, Tamaño) VALUES (?, ?)");

    if (!$stmtTamaño) {
        die("Error preparando statement para tamaños: " . $conn->error);
    }

    foreach ($_POST['Tamaño'] as $tam) {
        if (!empty($tam)) {
            $stmtTamaño->bind_param("is", $idProducto, $tam);
            $stmtTamaño->execute();
        }
    }

    $stmtTamaño->close();
}

// 6. Insertamos los colores seleccionados
if (!empty($_POST['colores']) && is_array($_POST['colores'])) {
    $stmtColor = $conn->prepare("INSERT INTO color (IDproducto, Color) VALUES (?, ?)");

    if (!$stmtColor) {
        die("Error preparando statement para colores: " . $conn->error);
    }

    foreach ($_POST['colores'] as $color) {
        if (!empty($color)) {
            $stmtColor->bind_param("is", $idProducto, $color);
            $stmtColor->execute();
        }
    }

    $stmtColor->close();
}
if (!empty($_POST['genero'])) {
    $stmtGenero = $conn->prepare("INSERT INTO genero (IDproducto, Genero) VALUES (?, ?)");

    if (!$stmtGenero) {
        die("Error preparando statement para género: " . $conn->error);
    }

    $stmtGenero->bind_param("is", $idProducto, $_POST['genero']);
    $stmtGenero->execute();
    $stmtGenero->close();
}


// 7. Subimos las imágenes
if (!empty($_FILES['imagenes']['name'][0])) {
    $stmtImg = $conn->prepare("INSERT INTO imagen (IDproducto, Imagen) VALUES (?, ?)");

    if (!$stmtImg) {
        die("Error preparando statement para imágenes: " . $conn->error);
    }

    foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
        if ($tmpName) {
            $nombreImagen = basename($_FILES['imagenes']['name'][$index]);
            $rutaDestino = "imagenes/" . uniqid() . "_" . $nombreImagen;

            if (move_uploaded_file($tmpName, $rutaDestino)) {
                $stmtImg->bind_param("is", $idProducto, $rutaDestino);
                $stmtImg->execute();
            }
        }
    }

    $stmtImg->close();
}

// 8. Redirigimos al panel principal
header("Location: ProcesarPanel.php");
exit();
?>
