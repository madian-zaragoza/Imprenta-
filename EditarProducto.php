<?php
session_start();

// Verificar si el usuario es un administrador
if (!isset($_SESSION['admin'])) {
    header("Location: InicioSesion.php");
    exit();
}

include 'Conexion.php';

// 1. Obtener el ID del producto desde la URL (si se proporciona)
$idProducto = $_GET['id'] ?? null; // Usamos GET para recibir el ID desde la URL
if (!$idProducto) {
    echo "ID de producto no recibido";
    exit();
}

// 2. Cargar los datos del producto desde la base de datos
$sql = "SELECT * FROM producto WHERE IDproducto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idProducto);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// 2.1 Cargar los elementos del producto (tamaño)
$sqlTamaño = "SELECT Tamaño FROM tamaño WHERE IDproducto = ?";
$stmtTamaño = $conn->prepare($sqlTamaño);
$stmtTamaño->bind_param("i", $idProducto);
$stmtTamaño->execute();
$resultTamaño = $stmtTamaño->get_result();

$tamanosSeleccionados = [];
while ($row = $resultTamaño->fetch_assoc()) {
    $tamanosSeleccionados[] = $row['Tamaño'];
}
$stmtTamaño->close();

// 2.2 Cargar los colores del producto
$sqlColor = "SELECT Color FROM color WHERE IDproducto = ?";
$stmtColor = $conn->prepare($sqlColor);
$stmtColor->bind_param("i", $idProducto);
$stmtColor->execute();
$resultColor = $stmtColor->get_result();

$coloresSeleccionados = [];
while ($row = $resultColor->fetch_assoc()) {
    $coloresSeleccionados[] = $row['Color'];
}
$stmtColor->close();

// 2.3 Cargar el género del producto
$sqlGenero = "SELECT Genero FROM genero WHERE IDproducto = ?";
$stmtGenero = $conn->prepare($sqlGenero);
$stmtGenero->bind_param("i", $idProducto);
$stmtGenero->execute();
$resultGenero = $stmtGenero->get_result();

$generoSeleccionado = $resultGenero->fetch_assoc()['Genero'] ?? '';
$stmtGenero->close();

// 2.4 Cargar las imágenes asociadas al producto
$sqlImagen = "SELECT Imagen FROM imagen WHERE IDproducto = ?";
$stmtImagen = $conn->prepare($sqlImagen);
$stmtImagen->bind_param("i", $idProducto);
$stmtImagen->execute();
$resultImagen = $stmtImagen->get_result();

$imagenes = [];
while ($row = $resultImagen->fetch_assoc()) {
    $imagenes[] = $row['Imagen'];
}
$stmtImagen->close();

// Si no se encuentra el producto
if (!$product) {
    echo "Producto no encontrado";
    exit();
}

// 3. Procesar el formulario de edición cuando se envíe
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $ventas = $_POST['ventas'] ?? 0;
    $disponible = $_POST['disponibilidad'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    // Actualizar los datos del producto
    $sqlUpdate = "UPDATE producto SET Nombre = ?, Tipo = ?, Precio = ?, Stock = ?, Ventas = ?, Disponibilidad = ?, Descripción = ? WHERE IDproducto = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssdiissi", $nombre, $tipo, $precio, $stock, $ventas, $disponible, $descripcion, $idProducto);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // Actualizar tamaños (si se recibió nueva info)
    if (isset($_POST['Tamaño']) && is_array($_POST['Tamaño'])) {
        $conn->query("DELETE FROM tamaño WHERE IDproducto = $idProducto");
        $stmtTamaño = $conn->prepare("INSERT INTO tamaño (IDproducto, Tamaño) VALUES (?, ?)");
        foreach ($_POST['Tamaño'] as $tam) {
            if (!empty($tam)) {
                $stmtTamaño->bind_param("is", $idProducto, $tam);
                $stmtTamaño->execute();
            }
        }
        $stmtTamaño->close();
    }

    // Actualizar colores (si se recibió nueva info)
    if (isset($_POST['colores']) && is_array($_POST['colores'])) {
        $conn->query("DELETE FROM color WHERE IDproducto = $idProducto");
        $stmtColor = $conn->prepare("INSERT INTO color (IDproducto, Color) VALUES (?, ?)");
        foreach ($_POST['colores'] as $color) {
            if (!empty($color)) {
                $stmtColor->bind_param("is", $idProducto, $color);
                $stmtColor->execute();
            }
        }
        $stmtColor->close();
    }

    // Actualizar género (solo 1)
    if (!empty($_POST['genero'])) {
        $conn->query("DELETE FROM genero WHERE IDproducto = $idProducto");
        $stmtGenero = $conn->prepare("INSERT INTO genero (IDproducto, Genero) VALUES (?, ?)");
        $stmtGenero->bind_param("is", $idProducto, $_POST['genero']);
        $stmtGenero->execute();
        $stmtGenero->close();
    }

    // Subir nuevas imágenes (si se recibió alguna)
    if (!empty($_FILES['imagenes']['name'][0])) {
        $conn->query("DELETE FROM imagen WHERE IDproducto = $idProducto");
        $stmtImg = $conn->prepare("INSERT INTO imagen (IDproducto, Imagen) VALUES (?, ?)");
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

    // Redirigir después de la actualización
    header("Location: ProcesarPanel.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto | Imprenta Cisneros</title>
    <link rel="shortcut icon" href="Logo/Recurso-8.ico" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #00a0e3;
            --secondary-color: #facf5a;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --border-radius: 4px;
            --box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f0f0;
            color: #333;
            line-height: 1.6;
        }
        
        header {
            background: linear-gradient(to right, rgba(214, 245, 255, 0.88), rgba(75, 207, 255, 0.85));
            color: white;
            padding: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .logo-container {
            margin-left: 20px;
            display: flex;
            align-items: center;
            display: flex; 
            justify-content: space-between;
        }
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo-container img {
            height: 60px;
            margin-right: 15px;
        }
        
        .header-btn {
            margin-right: 20px;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            padding: 8px 15px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        .header-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        .header-btn i {
            margin-right: 5px;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--box-shadow);
        }
        
        .section-title {
            color: var(--primary-color);
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 24px;
            font-weight: 600;
        }
        
        .section-title i {
            margin-right: 10px;
            background-color: var(--primary-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        
        .subtitle {
            color: #777;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
        }
        
        .card {
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-header i {
            background-color: var(--primary-color);
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 12px;
        }
        
        .card-header h3 {
            color: var(--primary-color);
            font-size: 18px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        
        label.required:after {
            content: ' *';
            color: var(--danger-color);
        }
        
        .help-text {
            font-size: 12px;
            color: #777;
            margin-bottom: 5px;
        }
        
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 160, 227, 0.1);
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .col {
            flex: 1;
            padding: 0 10px;
            min-width: 200px;
        }
        
        .mb-2 {
            margin-bottom: 10px;
        }
        
        .mb-3 {
            margin-bottom: 15px;
        }
        
        .input-group {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .input-group-text {
            background-color: #f5f5f5;
            border-right: 1px solid #ddd;
            padding: 10px;
            color: #777;
            display: flex;
        }
        
        .input-group input, 
        .input-group select {
            border: none;
            flex: 1;
        }
        
        .image-preview {
            margin-top: 10px;
            border: 1px dashed #ddd;
            border-radius: var(--border-radius);
            padding: 5px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9f9f9;
        }
        
        .preview-image {
            max-height: 100px;
            max-width: 100%;
            object-fit: contain;
        }
        
        .color-picker-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .color-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .color-picker {
            width: 50px;
            height: 50px;
            padding: 0;
            border: none;
            background: none;
            cursor: pointer;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-align: center;
        }
        
        .btn:hover {
            background-color: #0088c9;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            font-size: 12px;
            margin-right: 10px;
        }
        footer {
            background: linear-gradient(to right, rgb(246, 215, 143), rgb(250, 244, 157));
            color: #6c757d;
            padding: 20px 40px; /* Más espacio a los lados */
            margin-top: auto;
            height: 100px;
            display: flex;
            align-items: center;
        }
        .footer-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px; /* para que no se extienda demasiado en pantallas grandes */
            margin: 0 auto; /* centra el contenedor */
        }


    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="Logo/Recurso 5.png" alt="Imprenta Cisneros">
        </div>
        <a href="ProcesarPanel.php" class="header-btn">
            <i class="fas fa-arrow-left"></i> Volver al Panel
        </a>
    </header>

    <div class="container">
        <h1 class="section-title">
            <i class="fas fa-edit"></i>
            Editar Producto: <?php echo htmlspecialchars($product['Nombre']); ?>
        </h1>
        <p class="subtitle">Complete todos los campos necesarios para modificar este producto.</p>
        <div class="divider"></div>

        <form action="EditarProducto.php?id=<?php echo $idProducto; ?>" method="POST" enctype="multipart/form-data">
            <!-- ID del producto oculto -->
            <input type="hidden" name="idProducto" value="<?php echo $idProducto; ?>">

            <div class="card">
                <div class="card-header">
                    <div class="section-number">1</div>
                    <h3>Información Básica</h3>
                </div>
                
                <div class="form-group">
                    <label class="required">Nombre del Producto:</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($product['Nombre']); ?>" placeholder="Asigne un nombre descriptivo y único para el producto">
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="required">Tipo de Producto:</label>
                            <input type="text" name="tipo" value="<?php echo htmlspecialchars($product['Tipo']); ?>" placeholder="Categoría o tipo de producto (ej. Tarjeta, Folleto, Banner)">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="required">Precio ($):</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="precio" step="0.01" value="<?php echo htmlspecialchars($product['Precio']); ?>" placeholder="Precio de venta en pesos mexicanos">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="required">Género:</label>
                    <select name="genero">
                        <option value="Mujer" <?php echo $generoSeleccionado == 'Mujer' ? 'selected' : ''; ?>>Mujer</option>
                        <option value="Hombre" <?php echo $generoSeleccionado == 'Hombre' ? 'selected' : ''; ?>>Hombre</option>
                        <option value="Unisex" <?php echo $generoSeleccionado == 'Unisex' ? 'selected' : ''; ?>>Unisex</option>
                        <option value="No aplica" <?php echo $generoSeleccionado == 'No aplica' ? 'selected' : ''; ?>>No aplica</option>
                    </select>
                    <p class="help-text">Seleccione el género al que va dirigido el producto.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="section-number">2</div>
                    <h3>Características y Tamaños</h3>
                </div>
                
                <div class="form-group">
                    <label>Tamaños:</label>
                    <p class="help-text">Especifique hasta 6 dimensiones o tamaños disponibles para este producto.</p>
                    <div class="row">
                        <?php
                        // Mostrar hasta 6 campos, con los valores existentes si los hay
                        for ($i = 0; $i < 6; $i++) {
                            $valor = isset($tamanosSeleccionados[$i]) ? htmlspecialchars($tamanosSeleccionados[$i]) : '';
                            echo '
                            <div class="col mb-2">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-ruler-combined"></i></span>
                                    <input type="text" name="Tamaño[]" value="' . $valor . '" placeholder="Ej. S, 20x30 cm">
                                </div>
                            </div>';
                        }
                        ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Colores:</label>
                    <p class="help-text">Seleccione hasta 6 colores disponibles para este producto.</p>
                    <div class="color-picker-container">
                        <?php
                        // Mostrar hasta 6 selectores de color con valores existentes si los hay
                        for ($i = 0; $i < 6; $i++) {
                            $valor = isset($coloresSeleccionados[$i]) ? htmlspecialchars($coloresSeleccionados[$i]) : '#000000';
                            echo '
                            <div class="color-item">
                                <input type="color" name="colores[]" class="color-picker" value="' . $valor . '">
                            </div>';
                        }
                        ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción del Producto:</label>
                    <textarea name="descripcion" rows="5" placeholder="Describa las características, materiales, usos y beneficios del producto..."><?php echo htmlspecialchars($product['Descripción']); ?></textarea>
                    <p class="help-text">Una descripción detallada ayudará a los clientes a entender mejor el producto.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="section-number">3</div>
                    <h3>Inventario y Disponibilidad</h3>
                </div>
                
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="required">Stock Disponible:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-box"></i></span>
                                <input type="number" name="stock" value="<?php echo htmlspecialchars($product['Stock']); ?>" placeholder="Cantidad de unidades disponibles">
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Ventas:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-chart-line"></i></span>
                                <input type="number" name="ventas" value="<?php echo htmlspecialchars($product['Ventas']); ?>" placeholder="Ventas previas si existen">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="required">Disponibilidad:</label>
                   
                    <select name="disponibilidad" class="form-select">
    <option value="1" <?php echo $product['Disponibilidad'] == 1 ? 'selected' : ''; ?>>Disponible</option>
    <option value="0" <?php echo $product['Disponibilidad'] == 0 ? 'selected' : ''; ?>>No disponible</option>


                    </select>
                    <p class="help-text">Estado de disponibilidad en tienda.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="section-number">4</div>
                    <h3>Imágenes del Producto</h3>
                </div>
                
                <p class="help-text">Suba hasta 4 imágenes del producto. La primera será la imagen principal.</p>
                
                <div class="row">
                    <?php
                    for ($i = 0; $i < 4; $i++) {
                        $id = $i + 1;
                        $src = isset($imagenes[$i]) ? htmlspecialchars($imagenes[$i]) : '';
                        echo '
                        <div class="col mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-image"></i></span>
                                <input type="file" id="imagen' . $id . '" name="imagenes[]" accept="image/*" onchange="previewImage(this, \'preview' . $id . '\')">
                            </div>
                            <div id="preview' . $id . '" class="image-preview">
                                ' . ($src ? '<img class="preview-image" src="' . $src . '" alt="Vista previa">' : '<span class="text-muted">Vista previa</span>') . '
                            </div>
                        </div>';
                    }
                    ?>
                </div>
            </div>

            <div class="divider"></div>
            
            <div style="text-align: center;">
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
    <footer>
        <div class="footer-container">
            <span class="left">Sitio realizado por Huelicatl</span>
            <span class="right">© 2025 IMPRENTA CISNEROS S. DE R.L. DE C.V</span>
        </div>
    </footer>




    <script>
        // Función para mostrar vista previa de imágenes
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('preview-image');
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                const span = document.createElement('span');
                span.classList.add('text-muted');
                span.textContent = 'Vista previa';
                preview.appendChild(span);
            }
        }
    </script>
</body>
</html>
