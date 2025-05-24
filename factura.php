<?php
session_start();
include("Conexion.php");

$isLoggedIn = isset($_SESSION['usuario_id']);
if (!$isLoggedIn) {
    die("Debes iniciar sesión para ver tus compras.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de pedido inválido.");
}

$IDpedido = intval($_GET['id']);

// Obtener información del pedido
$sqlPedido = "SELECT P.*, U.Nombre AS UsuarioNombre 
              FROM pedido P
              JOIN usuario U ON P.IDusuario = U.IDusuario
              WHERE P.IDpedido = ?";
$stmt = $conn->prepare($sqlPedido);
$stmt->bind_param("i", $IDpedido);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    die("Pedido no encontrado.");
}

// Obtener detalles del pedido
$sqlDetalle = "SELECT D.*, Pr.Nombre AS Producto
               FROM pedidodetalle D
               JOIN producto Pr ON D.IDproducto = Pr.IDproducto
               WHERE D.IDpedido = ?";

$stmt = $conn->prepare($sqlDetalle);
$stmt->bind_param("i", $IDpedido);
$stmt->execute();
$detalles = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura del Pedido #<?= $pedido['IDpedido'] ?> | Imprenta Cisneros</title>
    <link rel="shortcut icon" href="Logo/Recurso-8.ico" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Variables de colores basados en el logo */
        :root {
            --cisneros-blue: #53b5e6;
            --cisneros-orange: #f06e0c;
            --cisneros-yellow: #ffd429;
            --cisneros-light-yellow: #fff29d;
            --cisneros-magenta: #e81289;
            --cisneros-dark: #222222;
            --cisneros-light: #f8f9fa;
            --cisneros-grey: #6c757d;
        }
        
        /* Estilos generales */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 80px;
            background-color: #fafafa;
            color: var(--cisneros-dark);
            position: relative;
            min-height: 100vh;
            padding-bottom: 120px;
        }

        /* ==================== */
        /* Estilos del encabezado */
        /* ==================== */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1050;
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
        }

        .logo-img {
            height: 60px;
            transition: transform 0.3s ease;
        }

        .logo-img:hover {
            transform: scale(1.05);
        }
        
        /* ==================== */
        /* Menú de usuario */
        /* ==================== */
        .user-menu {
            position: relative;
        }

        .user-menu button {
            background-color: white;
            color: var(--cisneros-dark);
            border: 2px solid var(--cisneros-orange);
            border-radius: 50%;
            width: 46px;
            height: 46px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .user-menu button:hover {
            background-color: var(--cisneros-orange);
            color: white;
        }

        .user-menu .fa-heart, 
        .user-menu .fa-shopping-cart {
            color: var(--cisneros-magenta);
            transition: all 0.3s ease;
        }

        .user-menu .fa-heart:hover, 
        .user-menu .fa-shopping-cart:hover {
            transform: scale(1.2);
        }

        .admin-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 10px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border: none;
            width: 220px;
            padding: 10px;
            background-color: white;
            display: none;
            z-index: 1060;
        }

        .admin-dropdown-item {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.2s ease;
        }

        .admin-dropdown-item:hover {
            background-color: #f0f0f0;
        }

        .admin-dropdown-item i {
            color: var(--cisneros-orange);
            width: 24px;
        }

        .admin-dropdown a {
            text-decoration: none;
            color: var(--cisneros-dark);
            display: block;
        }

        /* ==================== */
        /* Estilos de la factura */
        /* ==================== */
        .factura-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin: 30px auto;
            max-width: 1000px;
        }

        .factura-header {
            border-bottom: 2px solid var(--cisneros-light-yellow);
            margin-bottom: 25px;
            padding-bottom: 15px;
            position: relative;
        }

        .factura-title {
            color: var(--cisneros-dark);
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 5px;
            position: relative;
            display: inline-block;
        }

        .factura-title:after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 40%;
            height: 3px;
            background-color: var(--cisneros-magenta);
        }

        .factura-number {
            color: var(--cisneros-magenta);
            font-size: 1.3rem;
            font-weight: 600;
        }

        .factura-info {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .factura-info-group {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 15px;
            flex: 1;
            min-width: 280px;
            margin-right: 15px;
            border-left: 4px solid var(--cisneros-yellow);
        }

        .factura-info-group:last-child {
            margin-right: 0;
        }

        .factura-info-label {
            font-weight: 600;
            color: var(--cisneros-dark);
            margin-bottom: 5px;
            display: block;
        }

        .factura-info-value {
            color: var(--cisneros-grey);
        }

        /* Tabla de productos */
        .factura-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .factura-table thead th {
            background-color: var(--cisneros-blue);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }

        .factura-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .factura-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .factura-table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .factura-table td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .color-box {
            width: 25px;
            height: 25px;
            display: inline-block;
            border-radius: 4px;
            border: 1px solid #ddd;
            vertical-align: middle;
            margin-right: 8px;
        }

        /* Resumen total */
        .factura-total {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            text-align: right;
            margin-top: 30px;
            border-right: 4px solid var(--cisneros-magenta);
        }

        .factura-total h2 {
            color: var(--cisneros-dark);
            font-size: 1.5rem;
            margin: 0;
        }

        .factura-total .amount {
            color: var(--cisneros-magenta);
            font-weight: 700;
            font-size: 1.8rem;
        }

        /* Botón de impresión */
        .btn-print {
            background: linear-gradient(135deg, var(--cisneros-blue), var(--cisneros-magenta));
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: block;
            margin: 30px auto;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-print i {
            margin-right: 8px;
        }

        /* Footer */
        footer {
            background: linear-gradient(to right, rgb(246, 215, 143), rgb(250, 244, 157));
            color: #6c757d;
            padding: 20px 40px;
            height: 100px;
            display: flex;
            align-items: center;
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
/* Variables de colores basados en el logo */
:root {
    --cisneros-blue: #53b5e6;
    --cisneros-orange: #f06e0c;
    --cisneros-yellow: #ffd429;
    --cisneros-light-yellow: #fff29d;
    --cisneros-magenta: #e81289;
    --cisneros-dark: #222222;
    --cisneros-light: #f8f9fa;
    --cisneros-grey: #6c757d;
}

/* Estilos generales */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding-top: 80px;
    background-color: #fafafa;
    color: var(--cisneros-dark);
    position: relative;
    min-height: 100vh;
    padding-bottom: 120px;
}

/* ==================== */
/* Estilos del encabezado */
/* ==================== */
.header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1050;
    background-color: white;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    padding: 10px 0;
}

.logo-img {
    height: 60px;
    transition: transform 0.3s ease;
}

.logo-img:hover {
    transform: scale(1.05);
}

/* ==================== */
/* Menú de usuario */
/* ==================== */
.user-menu {
    position: relative;
}

.user-menu button {
    background-color: white;
    color: var(--cisneros-dark);
    border: 2px solid var(--cisneros-orange);
    border-radius: 50%;
    width: 46px;
    height: 46px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.user-menu button:hover {
    background-color: var(--cisneros-orange);
    color: white;
}

.user-menu .fa-heart, 
.user-menu .fa-shopping-cart {
    color: var(--cisneros-magenta);
    transition: all 0.3s ease;
}

.user-menu .fa-heart:hover, 
.user-menu .fa-shopping-cart:hover {
    transform: scale(1.2);
}

.admin-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 10px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border: none;
    width: 220px;
    padding: 10px;
    background-color: white;
    display: none;
    z-index: 1060;
}

.admin-dropdown-item {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 5px;
    transition: all 0.2s ease;
}

.admin-dropdown-item:hover {
    background-color: #f0f0f0;
}

.admin-dropdown-item i {
    color: var(--cisneros-orange);
    width: 24px;
}

.admin-dropdown a {
    text-decoration: none;
    color: var(--cisneros-dark);
    display: block;
}

/* ==================== */
/* Estilos de la factura */
/* ==================== */
.factura-container {
    background-color: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    padding: 30px;
    margin: 30px auto;
    max-width: 1000px;
}

.factura-header {
    border-bottom: 2px solid var(--cisneros-light-yellow);
    margin-bottom: 25px;
    padding-bottom: 15px;
    position: relative;
}

.factura-title {
    color: var(--cisneros-dark);
    font-weight: 700;
    font-size: 28px;
    margin-bottom: 5px;
    position: relative;
    display: inline-block;
}

.factura-title:after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 40%;
    height: 3px;
    background-color: var(--cisneros-magenta);
}

.factura-number {
    color: var(--cisneros-magenta);
    font-size: 1.3rem;
    font-weight: 600;
}

.factura-info {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin-bottom: 30px;
}

.factura-info-group {
    background-color: #f9f9f9;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 15px;
    flex: 1;
    min-width: 280px;
    margin-right: 15px;
    border-left: 4px solid var(--cisneros-yellow);
}

.factura-info-group:last-child {
    margin-right: 0;
}

.factura-info-label {
    font-weight: 600;
    color: var(--cisneros-dark);
    margin-bottom: 5px;
    display: block;
}

.factura-info-value {
    color: var(--cisneros-grey);
}

/* Tabla de productos */
.factura-table {
    width: 100%;
    border-collapse: collapse;
    margin: 25px 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.factura-table thead th {
    background-color: var(--cisneros-blue);
    color: white;
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
}

.factura-table tbody tr {
    border-bottom: 1px solid #f0f0f0;
}

.factura-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.factura-table tbody tr:hover {
    background-color: #f5f5f5;
}

.factura-table td {
    padding: 12px 15px;
    vertical-align: middle;
}

.color-box {
    width: 25px;
    height: 25px;
    display: inline-block;
    border-radius: 4px;
    border: 1px solid #ddd;
    vertical-align: middle;
    margin-right: 8px;
}

/* Resumen total */
.factura-total {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 20px;
    text-align: right;
    margin-top: 30px;
    border-right: 4px solid var(--cisneros-magenta);
}

.factura-total h2 {
    color: var(--cisneros-dark);
    font-size: 1.5rem;
    margin: 0;
}

.factura-total .amount {
    color: var(--cisneros-magenta);
    font-weight: 700;
    font-size: 1.8rem;
}

/* Botón de impresión */
.btn-print {
    background: linear-gradient(135deg, var(--cisneros-blue), var(--cisneros-magenta));
    color: white;
    border: none;
    border-radius: 30px;
    padding: 12px 25px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: block;
    margin: 30px auto;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.btn-print:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

.btn-print i {
    margin-right: 8px;
}

/* Footer */
footer {
    background: linear-gradient(to right, rgb(246, 215, 143), rgb(250, 244, 157));
    color: #6c757d;
    padding: 20px 40px;
    height: 100px;
    display: flex;
    align-items: center;
    position: absolute;
    bottom: 0;
    width: 100%;
}

.footer-container {
    display: flex;
    justify-content: space-between;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

/* ==================== */
/* ESTILOS RESPONSIVOS MEJORADOS */
/* ==================== */

/* Tablets y dispositivos medianos */
@media (max-width: 992px) {
    /* Ajustamos el header */
    .header {
        padding: 12px 0;
    }
    
    .logo-img {
        height: 50px;
    }
    
    /* Contenedor de factura */
    .factura-container {
        padding: 25px;
        margin: 25px auto;
    }
    
    /* Ajustar grupos de información */
    .factura-info-group {
        min-width: 230px;
        margin-right: 10px;
    }
    
    /* Títulos y fuentes */
    .factura-title {
        font-size: 26px;
    }
    
    .factura-number {
        font-size: 1.2rem;
    }
}

/* Móviles y dispositivos pequeños */
@media (max-width: 768px) {
    /* General */
    body {
        padding-top: 70px;
        padding-bottom: 150px; /* Espacio para footer */
        font-size: 16px;
    }
    
    /* Header ajustado */
    .header {
        padding: 10px 0;
    }
    
    .logo-img {
        height: 45px;
    }
    
    /* Menú de usuario */
    .user-menu button {
        width: 52px;
        height: 52px;
    }
    
    .admin-dropdown {
        width: 260px;
        padding: 15px;
        right: -10px;
    }
    
    .admin-dropdown-item {
        padding: 15px;
        margin-bottom: 8px;
        min-height: 54px; /* Área táctil adecuada */
        font-size: 16px;
    }
    
    /* Contenedor de factura */
    .factura-container {
        padding: 20px 15px;
        margin: 15px auto;
        border-radius: 12px;
    }
    
    /* Header de factura */
    .factura-header {
        margin-bottom: 20px;
        padding-bottom: 10px;
    }
    
    .factura-title {
        font-size: 24px;
    }
    
    .factura-number {
        font-size: 1.1rem;
    }
    
    /* Grupos de información */
    .factura-info {
        flex-direction: column;
        margin-bottom: 20px;
    }
    
    .factura-info-group {
        flex: 100%;
        margin-right: 0;
        width: 100%;
        margin-bottom: 15px;
        padding: 15px;
    }
    
    .factura-info-label {
        font-size: 16px;
        margin-bottom: 3px;
    }
    
    .factura-info-value {
        font-size: 16px;
        line-height: 1.6;
    }
    
    /* Tabla de productos */
    .factura-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
        margin: 15px 0;
        font-size: 15px;
    }
    
    .factura-table thead th {
        padding: 15px;
        font-size: 16px;
    }
    
    .factura-table td {
        padding: 12px 15px;
    }
    
    .color-box {
        width: 24px;
        height: 24px;
    }
    
    /* Total */
    .factura-total {
        padding: 18px;
        margin-top: 25px;
        text-align: center; /* Centrar en móvil */
    }
    
    .factura-total h2 {
        font-size: 20px;
    }
    
    .factura-total .amount {
        font-size: 26px;
    }
    
    /* Botón de impresión */
    .btn-print {
        width: 100%;
        padding: 15px 25px;
        font-size: 18px;
        margin: 25px auto;
        min-height: 54px; /* Área táctil adecuada */
    }
    
    /* Footer */
    footer {
        padding: 15px;
        height: auto;
        min-height: 100px;
    }
    
    .footer-container {
        flex-direction: column;
        text-align: center;
    }
    
    .footer-container div {
        margin-bottom: 10px;
    }
}

/* Móviles muy pequeños */
@media (max-width: 480px) {
    /* General */
    body {
        font-size: 16px;
    }
    
    /* Header */
    .logo-img {
        height: 40px;
    }
    
    /* Contenedor de factura */
    .factura-container {
        padding: 15px 12px;
        margin: 12px auto;
    }
    
    /* Header de factura */
    .factura-title {
        font-size: 22px;
        margin-bottom: 3px;
    }
    
    .factura-title:after {
        height: 2px;
    }
    
    .factura-number {
        font-size: 16px;
    }
    
    /* Tabla de productos */
    .factura-table {
        font-size: 14px;
    }
    
    .factura-table thead th {
        padding: 12px 10px;
        font-size: 15px;
    }
    
    .factura-table td {
        padding: 10px;
    }
    
    /* Optimización para tablas en dispositivos muy pequeños */
    .factura-table-container {
        margin: 0 -12px; /* Extender tabla más allá de los márgenes */
        overflow-x: auto;
        padding: 0 12px;
    }
    
    /* Mejora visual para tablas en móvil */
    .factura-table-mobile-wrapper {
        position: relative;
    }
    
    .factura-table-mobile-wrapper:before,
    .factura-table-mobile-wrapper:after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        width: 30px;
        z-index: 2;
        pointer-events: none;
    }
    
    .factura-table-mobile-wrapper:before {
        left: 0;
        background: linear-gradient(to right, rgba(255,255,255,1), rgba(255,255,255,0));
    }
    
    .factura-table-mobile-wrapper:after {
        right: 0;
        background: linear-gradient(to left, rgba(255,255,255,1), rgba(255,255,255,0));
    }
    
    /* Total */
    .factura-total {
        padding: 15px;
    }
    
    .factura-total h2 {
        font-size: 18px;
    }
    
    .factura-total .amount {
        font-size: 24px;
    }
    
    /* Menú de usuario más compacto */
    .user-menu a {
        margin-right: 10px;
    }
    
    .user-menu button {
        width: 44px;
        height: 44px;
    }
}

/* Orientación horizontal en móviles */
@media (max-width: 992px) and (orientation: landscape) {
    body {
        padding-bottom: 140px;
    }
    
    .factura-info {
        flex-direction: row;
    }
    
    .factura-info-group {
        width: calc(50% - 10px);
        flex: 0 0 calc(50% - 10px);
        margin-right: 10px;
    }
    
    .factura-info-group:nth-child(2n) {
        margin-right: 0;
    }
    
    footer {
        height: auto;
        min-height: 80px;
    }
}

/* Estilos para impresión */
@media print {
    body {
        padding: 0;
        background: white;
    }
    
    .header, .btn-print, footer, .user-menu {
        display: none !important;
    }
    
    .factura-container {
        box-shadow: none;
        padding: 0;
        margin: 0;
        max-width: 100%;
    }
    
    .factura-title {
        font-size: 24px;
    }
    
    .factura-table {
        page-break-inside: avoid;
    }
    
    .factura-total {
        margin-top: 20px;
    }
}
    </style>
</head>
<body>
    <header id="header1" class="header px-4 py-2">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Logo -->
                <div>
                    <a href="Imprenta_Cisneros.php" class="logo">
                        <img src="Logo/Recurso 5.png" alt="Imprenta Cisneros Logo" class="logo-img">   
                    </a>
                </div>
                
                <!-- User Menu -->
                <div class="user-menu d-flex align-items-center">
                    <?php if ($isLoggedIn): ?>
                    <a href="favoritos.php" class="me-3" title="Favoritos">
                        <i class="fas fa-heart fa-lg"></i>
                    </a>
                    <a href="carrito.php" class="me-3" title="Carrito">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                    </a>
                    <?php endif; ?>
                    
                    <div class="position-relative">
                        <button id="userToggle" class="btn">
                            <i class="fas fa-user-circle fa-lg"></i>
                        </button>
                        <div id="userMenu" class="admin-dropdown">
                            <?php if ($isLoggedIn): ?>
                                <div class="admin-dropdown-item"><i class="fas fa-user"></i><?php echo htmlspecialchars($_SESSION['nombre']); ?></div>
                                <div class="admin-dropdown-item"><a href="Pedidos.php"><i class="fas fa-box"></i> Pedidos</a></div>
                                <div class="admin-dropdown-item"><a href="Cerrar.php"><i class="fas fa-sign-out-alt"></i>Cerrar sesión</a></div>
                            <?php else: ?>
                                <div class="admin-dropdown-item"><a href="Iniciarsesion.php"><i class="fas fa-sign-in-alt"></i>Iniciar sesión</a></div>
                                <div class="admin-dropdown-item"><a href="Registro.php"><i class="fas fa-user-plus"></i>Registrarse</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container factura-container">
        <div class="factura-header">
            <h1 class="factura-title">Factura del Pedido</h1>
        </div>

        <div class="factura-info">
            <div class="factura-info-group">
                <span class="factura-info-label"><i class="fas fa-user me-2"></i>Información del Cliente</span>
                <p class="factura-info-value mb-1"><strong>Cliente:</strong> <?= htmlspecialchars($pedido['UsuarioNombre']) ?></p>
                <p class="factura-info-value mb-1"><strong>Remitente:</strong> <?= htmlspecialchars($pedido['Remitente']) ?></p>
                <p class="factura-info-value"><strong>Teléfono:</strong> <?= htmlspecialchars($pedido['Telefono']) ?></p>
            </div>

            <div class="factura-info-group">
                <span class="factura-info-label"><i class="fas fa-map-marker-alt me-2"></i>Dirección de Envío</span>
                <p class="factura-info-value"><?= nl2br(htmlspecialchars($pedido['Direccion'])) ?></p>
            </div>

            <div class="factura-info-group">
                <span class="factura-info-label"><i class="fas fa-calendar-alt me-2"></i>Información del Pedido</span>
                <p class="factura-info-value"><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($pedido['Fecha'])) ?></p>
                <p class="factura-info-value"><strong>Número de Pedido:</strong> <?= $pedido['IDpedido'] ?></p>
            </div>
        </div>

        <table class="factura-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Color</th>
            <th>Tamaño</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $total = 0;
    while ($row = $detalles->fetch_assoc()):
        $precioUnitario = floatval($row['PrecioUnitario']);
        $cantidad = intval($row['Cantidad']);
        $subtotal = $precioUnitario * $cantidad;
        $total += $subtotal;
    ?>
        <tr>
            <td><?= htmlspecialchars($row['Producto']) ?></td>
            <td>
                <?php if (!empty($row['Color'])): ?>
                    <div class="color-box" style="background-color: <?= htmlspecialchars($row['Color']) ?>"></div>
                    <?= htmlspecialchars($row['Color']) ?>
                <?php else: ?>
                    <span>Sin color</span>
                <?php endif; ?>
            </td>
            <td><?= !empty($row['Tamaño']) ? htmlspecialchars($row['Tamaño']) : 'Sin tamaño' ?></td>
            <td class="text-center"><?= $cantidad ?></td>
            <td class="text-end">$<?= number_format($precioUnitario, 2) ?></td>
            <td class="text-end">$<?= number_format($subtotal, 2) ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>


        <div class="factura-total">
            <h2>Total: <span class="amount">$<?= number_format($total, 2) ?></span></h2>
        </div>

        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print"></i> Imprimir Factura
        </button>
    </div>

    <footer>
        <div class="footer-container">
            <span class="left">Sitio realizado por Huelicatl</span>
            <span class="right">© 2025 IMPRENTA CISNEROS S. DE R.L. DE C.V</span>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar menú de usuario
        document.getElementById('userToggle').addEventListener('click', function() {
            const menu = document.getElementById('userMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userToggle = document.getElementById('userToggle');
            
            if (userMenu.style.display === 'block' && 
                !userMenu.contains(event.target) && 
                !userToggle.contains(event.target)) {
                userMenu.style.display = 'none';
            }
        });
    </script>
</body>
</html>