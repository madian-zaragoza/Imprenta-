<?php
session_start();
include 'Conexion.php';

$isLoggedIn = isset($_SESSION['usuario_id']);

// Paginación
$productosPorPagina = 20;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $productosPorPagina;

// Obtener parámetros de búsqueda y filtros
$busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';
$filtroGenero = isset($_GET['genero']) ? $_GET['genero'] : '';
$filtroTipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Construir cláusulas WHERE dinámicas
$whereClauses = [];

if (!empty($filtroGenero)) {
    $whereClauses[] = "g.Genero = '" . $conn->real_escape_string($filtroGenero) . "'";
}
if (!empty($filtroTipo)) {
    $whereClauses[] = "p.Tipo = '" . $conn->real_escape_string($filtroTipo) . "'";
}
if (!empty($busqueda)) {
    $whereClauses[] = "(p.Nombre LIKE '%$busqueda%' OR p.Tipo LIKE '%$busqueda%' OR g.Genero LIKE '%$busqueda%' OR p.Descripción LIKE '%$busqueda%')";
}

$whereSQL = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Total de productos para paginación
$sqlTotal = "
    SELECT COUNT(DISTINCT p.IDproducto) AS total
    FROM producto p
    LEFT JOIN genero g ON p.IDproducto = g.IDproducto
    $whereSQL
";
$totalResult = $conn->query($sqlTotal);
$totalProductos = $totalResult->fetch_assoc()['total'];
$totalPaginas = ($productosPorPagina > 0) ? ceil($totalProductos / $productosPorPagina) : 1;

// Consulta de productos con filtros y paginación
$sql = "
    SELECT 
        p.IDproducto, 
        p.Nombre, 
        p.Precio, 
        p.Tipo,
        GROUP_CONCAT(DISTINCT g.Genero) AS Generos,
        i.Imagen,
        GROUP_CONCAT(DISTINCT c.Color) AS Colores
    FROM producto p
    LEFT JOIN imagen i ON p.IDproducto = i.IDproducto
    LEFT JOIN color c ON p.IDproducto = c.IDproducto
    LEFT JOIN genero g ON p.IDproducto = g.IDproducto
    $whereSQL
    GROUP BY p.IDproducto
    LIMIT $productosPorPagina OFFSET $offset
";
$result = $conn->query($sql);

// Productos más vendidos
$sqlMasVendidos = "
    SELECT 
        p.IDproducto, 
        p.Nombre, 
        p.Precio, 
        MIN(i.Imagen) AS Imagen
    FROM producto p
    LEFT JOIN imagen i ON p.IDproducto = i.IDproducto
    GROUP BY p.IDproducto, p.Nombre, p.Precio
    ORDER BY p.Ventas DESC
    LIMIT 6
";

$masVendidos = $conn->query($sqlMasVendidos);

$productos = array();

// Guardamos los resultados en el array de productos
while ($producto = $masVendidos->fetch_assoc()) {
    $productos[] = $producto;
}



// Guardamos los resultados en el array de productos
while ($producto = $masVendidos->fetch_assoc()) {
    $productos[] = $producto;
}



// Filtros disponibles
$generos = $conn->query("SELECT DISTINCT Genero FROM genero");
$tipos = $conn->query("SELECT DISTINCT Tipo FROM producto WHERE Tipo IS NOT NULL AND Tipo != ''");
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Imprenta Cisneros</title>
    <link rel="shortcut icon" href="Logo/Recurso-8.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Variables de colores y tamaños */
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
/* Menú de navegación principal */
/* ==================== */
.main-menu .dropdown-toggle {
    background-color: var(--cisneros-yellow);
    border-color: var(--cisneros-yellow);
    color: white;
    font-weight: 600;
    border-radius: 25px;
}

.main-menu .dropdown-toggle:hover, 
.main-menu .dropdown-toggle:focus {
    background-color:rgb(255, 225, 77);
    border-color: rgb(255, 219, 77);
}

.main-menu .dropdown-menu {
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border: none;
    padding: 10px;
    z-index: 1060;
}

.main-menu .dropdown-item {
    padding: 8px 20px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.main-menu .dropdown-item:hover {
    background-color: var(--cisneros-light-yellow);
    transform: translateX(5px);
}

/* ==================== */
/* Menú flotante */
/* ==================== */
.floating-menu {
    position: fixed;
    left: 20px;
    top: 100px;
    z-index: 1000;
    transition: all 0.3s ease;
}

.floating-menu .main-menu .dropdown-toggle {
    background-color: rgba(248, 169, 32, 0.89);
    border-color: rgba(248, 169, 32, 0.86);
    color: black;
    font-weight: 600;
    border-radius: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.floating-menu .main-menu .dropdown-toggle:hover, 
.floating-menu .main-menu .dropdown-toggle:focus {
    background-color: rgba(248, 201, 32, 0.8);
    border-color: rgba(248, 201, 32, 0.8);
    transform: scale(1.05);
}

.floating-menu .main-menu .dropdown-menu {
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border: none;
    padding: 10px;
    z-index: 1060;
}

.floating-menu .main-menu .dropdown-item {
    padding: 8px 20px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.floating-menu .main-menu .dropdown-item:hover {
    background-color: var(--cisneros-light-yellow);
    transform: translateX(5px);
}

/* Animación para el menú flotante */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
    100% { transform: translateY(0px); }
}

.floating-menu:hover {
    animation: float 2s ease-in-out infinite;
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
/* Banner Hero */
/* ==================== */
.hero-banner {
    position: relative;
    height: 400px;
    overflow: hidden;
    border-radius: 0 0 30px 30px;
    margin-bottom: 50px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.hero-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(83, 181, 230, 0.7) 0%, rgba(240, 110, 12, 0.5) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-content {
    text-align: center;
    color: white;
    padding: 20px;
    max-width: 800px;
}

.hero-content h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.hero-content p {
    font-size: 20px;
    margin-bottom: 25px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
}

.hero-btn {
    background-color: rgba(248, 169, 32, 0.8);;
    color: var(--cisneros-dark);
    font-weight: 600;
    padding: 12px 30px;
    border-radius: 30px;
    border: none;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.hero-btn:hover {
    background-color: #e5bc16;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* ==================== */
/* Sección de títulos */
/* ==================== */
.section-title {
    position: relative;
    text-align: center;
    font-weight: 700;
    margin-bottom: 30px;
    color: var(--cisneros-dark);
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(to right, var(--cisneros-orange), var(--cisneros-magenta));
    border-radius: 2px;
}

/* ==================== */
/* Sección de Carrusel */
/* ==================== */
.carousel-container {
    background-color: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    margin-bottom: 50px;
}

.carousel-control-prev, 
.carousel-control-next {
    width: 50px;
    height: 50px;
    background-color:  rgba(248, 169, 32, 0.86);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.8;
}

.carousel-control-prev {
    left: -25px;
}

.carousel-control-next {
    right: -25px;
}

.carousel-control-prev:hover, 
.carousel-control-next:hover {
    opacity: 1;
}

.carousel-control-prev-icon, 
.carousel-control-next-icon {
    width: 25px;
    height: 25px;
}

.carousel-product {
    text-align: center;
    padding: 15px;
    transition: transform 0.3s ease;
}

.carousel-product:hover {
    transform: translateY(-10px);
}

.carousel-product img {
    max-height: 200px;
    object-fit: contain;
    margin-bottom: 15px;
}

.carousel-product h5 {
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--cisneros-dark);
}

.carousel-product .price {
    font-size: 20px;
    font-weight: 700;
    color: var(--cisneros-magenta);
}

/* ==================== */
/* Búsqueda y Filtros */
/* ==================== */
.search-filter-container {
    background-color: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    margin-bottom: 40px;
}

.search-input {
    border: 2px solid var(--cisneros-light-yellow);
    border-radius: 25px;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: var(--cisneros-yellow);
    box-shadow: 0 0 0 0.25rem rgba(255, 212, 41, 0.25);
}

.search-btn {
    background-color:  rgba(248, 169, 32, 0.8);
    border: none;
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
}

.search-btn:hover {
    background-color:  rgba(248, 169, 32, 0.92);
}

.filter-select {
    border: 2px solid var(--cisneros-light-yellow);
    border-radius: 25px;
    padding: 10px 20px;
}

.filter-select:focus {
    border-color: var(--cisneros-yellow);
    box-shadow: 0 0 0 0.25rem rgba(255, 212, 41, 0.25);
}

.filter-btn {
    background-color: rgba(248, 169, 32, 0.8);
    border: none;
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
}

.filter-btn:hover {
    background-color:  rgba(248, 169, 32, 0.92);
}

/* ==================== */
/* Rejilla de Productos */
/* ==================== */
.products-container {
    background-color: white;
    border-radius: 20px;
    padding: 40px 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    margin-bottom: 60px;
}

.product-card {
    background-color: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    margin-bottom: 30px;
    height: 100%;
    position: relative;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
}

.product-image-container {
    height: 200px;
    overflow: hidden;
    background-color: #f8f9fa;
    position: relative;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image {
    transform: scale(1.1);
}

.product-details {
    padding: 20px;
    text-align: center;
}

.product-title {
    font-weight: 600;
    font-size: 18px;
    margin-bottom: 10px;
    color: var(--cisneros-dark);
    height: 50px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-price {
    font-size: 18px;
    font-weight: 700;
    color: var(--cisneros-magenta);
    margin-bottom: 15px;
}

/* Opciones de color del producto */
.color-options {
    margin-top: 15px;
}

.color-circle {
    width: 20px;
    height: 20px;
    display: inline-block;
    border-radius: 50%;
    border: 2px solid #f0f0f0;
    margin: 0 3px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.color-circle:hover {
    transform: scale(1.3);
}

.favorito {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 10;
    background-color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    font-size: 20px;
    color: #ccc;
}

.favorito:hover {
    transform: scale(1.15);
}

.favorito.activo {
    color: var(--cisneros-magenta);
}

/* ==================== */
/* Paginación */
/* ==================== */
.pagination-container {
    margin-top: 30px;
    margin-bottom: 20px;
}

.pagination-btn {
    margin: 0 5px;
    min-width: 40px;
    height: 40px;
    border-radius: 20px;
    background-color: white;
    border: 2px solid var(--cisneros-light-yellow);
    color: var(--cisneros-dark);
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination-btn:hover, 
.pagination-btn.active {
    background-color: var(--cisneros-yellow);
    border-color: var(--cisneros-yellow);
    color: var(--cisneros-dark);
}

/* ==================== */
/* Sección Sobre Nosotros */
/* ==================== */
.about-us-container {
    background: linear-gradient(135deg, rgba(83, 181, 230, 0.05) 0%, rgba(240, 110, 12, 0.05) 100%);
    border-radius: 20px;
    padding: 50px 30px;
    margin-bottom: 60px;
}

.about-us-content {
    padding-right: 30px;
}

.about-us-title {
    position: relative;
    font-weight: 700;
    margin-bottom: 25px;
    padding-bottom: 15px;
    color: var(--cisneros-dark);
}

.about-us-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 80px;
    height: 4px;
    background: linear-gradient(to right, var(--cisneros-blue), var(--cisneros-magenta));
    border-radius: 2px;
}

.about-us-text {
    font-size: 16px;
    line-height: 1.8;
    margin-bottom: 20px;
    color: #555;
}

.about-us-highlight {
    font-weight: 600;
    color: var(--cisneros-dark);
}

.about-us-image {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    height: 100%;
}

.about-us-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.about-us-image:hover img {
    transform: scale(1.05);
}

/* ==================== */
/* Footer */
/* ==================== */
.footer {
   background: linear-gradient(to right, rgba(12, 170, 218, 0.68), rgba(122, 220, 255, 0.64));
    color: var(--cisneros-dark);
    padding: 60px 0 30px;
    border-radius: 30px 30px 0 0;
    margin-top: 60px;
}

.footer-map {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    height: 300px;
}

.footer-title {
    font-weight: 700;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--cisneros-magenta);
    border-radius: 2px;
}

.footer-info {
    margin-bottom: 20px;
}

.footer-credits {
    padding-top: 30px;
    margin-top: 30px;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.footer-credits span {
    font-size: 14px;
    color: #555;
}

/* ==================== */
/* Ajustes Responsivos */
/* ==================== */
/* ======================================
   ESTILOS RESPONSIVOS PARA TABLETAS
   ====================================== */
@media (max-width: 992px) {
    /* Header */
    .logo-img {
        height: 50px;
    }

    /* Menú usuario */
    .user-menu button {
        width: 44px;
        height: 44px;
    }

    /* Hero Banner */
    .hero-banner {
        height: 350px;
        margin-bottom: 40px;
    }

    .hero-content h1 {
        font-size: 36px;
    }
    
    .hero-content p {
        font-size: 18px;
    }

    /* Carrusel */
    .carousel-container {
        padding: 25px 15px;
    }

    .carousel-product img {
        max-height: 180px;
    }

    /* Productos */
    .products-container {
        padding: 30px 20px;
    }

    .product-image-container {
        height: 180px;
    }

    .product-title {
        font-size: 16px;
        height: 44px;
    }

    /* Filtros */
    .search-filter-container {
        padding: 20px;
    }

    /* About us */
    .about-us-container {
        padding: 40px 25px;
    }

    .about-us-content {
        padding-right: 0;
        margin-bottom: 30px;
    }
}

/* ======================================
   ESTILOS RESPONSIVOS PARA MÓVILES - OPTIMIZADOS
   ====================================== */
@media (max-width: 768px) {
    /* General */
    ```css
/* ======================================
   OPTIMIZACIÓN DEL HEADER PARA MÓVILES
   ====================================== */
@media (max-width: 768px) {
    /* Header principal */
    .header {
        padding: 12px 0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1030;
        background-color: #fff;
    }
    
    /* Contenedor del header con más espacio horizontal */
    .header .container {
        padding-left: 20px;
        padding-right: 20px;
    }
    
    /* Logo más visible */
    .logo-img {
        height: 40px; /* Tamaño equilibrado */
        margin-right: 15px;
    }
    
    /* Menú de usuario con elementos más grandes */
    .user-menu {
        display: flex;
        align-items: center;
        gap: 15px; /* Espacio uniforme entre elementos */
    }
    
    /* Iconos del menú más grandes y accesibles */
    .user-menu a,
    .user-menu button {
        width: 44px;
        height: 44px;
        font-size: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f5f5f5;
        color: #333;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        position: relative;
    }
    
    /* Estilo activo para botones */
    .user-menu a:active,
    .user-menu button:active {
        transform: scale(0.95);
        background-color: #e0e0e0;
    }
    
    /* Contador de notificaciones o carrito */
    .notification-count,
    .cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #ff4747;
        color: white;
        font-size: 12px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border: 2px solid white;
    }
    
    /* Menú hamburguesa más grande */
    .menu-toggle {
        width: 44px;
        height: 44px;
        font-size: 22px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: transparent;
        border: none;
    }
    
    /* Buscador en header */
    .header-search {
        position: relative;
        flex-grow: 1;
        margin: 0 15px;
    }
    
    .header-search-input {
        width: 100%;
        height: 44px;
        padding: 8px 15px;
        padding-right: 44px; /* Espacio para el icono */
        border-radius: 22px;
        border: 1px solid #ddd;
        font-size: 16px;
        background-color: #f5f5f5;
    }
    
    .header-search-btn {
        position: absolute;
        right: 4px;
        top: 4px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background-color: #007bff;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    /* Menú de navegación móvil (desplegable) */
    .mobile-nav {
        position: fixed;
        top: 76px; /* Altura del header + padding */
        left: 0;
        width: 100%;
        background-color: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        z-index: 1025;
        border-radius: 0 0 20px 20px;
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.3s ease-out;
    }
    
    .mobile-nav.open {
        max-height: 400px; /* Altura máxima del menú desplegado */
        overflow-y: auto;
    }
    
    .mobile-nav-item {
        padding: 16px 24px;
        font-size: 18px;
        border-bottom: 1px solid #eee;
        display: block;
        color: #333;
        text-decoration: none;
    }
    
    .mobile-nav-item:last-child {
        border-bottom: none;
    }
    
    /* Dropdown de usuario optimizado */
    .user-dropdown {
        position: absolute;
        top: 60px;
        right: 10px;
        width: 220px;
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        padding: 12px 0;
        z-index: 1040;
        display: none;
    }
    
    .user-dropdown.show {
        display: block;
        animation: fadeIn 0.2s ease-out;
    }
    
    .user-dropdown-item {
        padding: 14px 20px;
        font-size: 16px;
        color: #333;
        display: flex;
        align-items: center;
        text-decoration: none;
    }
    
    .user-dropdown-item i {
        margin-right: 12px;
        font-size: 18px;
        width: 20px;
        text-align: center;
    }
    
    .user-dropdown-item:active {
        background-color: #f5f5f5;
    }
    
    /* Separador en el dropdown */
    .dropdown-divider {
        height: 1px;
        background-color: #eee;
        margin: 8px 0;
    }
    
    /* Animación para el dropdown */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
}

/* Para móviles más pequeños */
@media (max-width: 576px) {
    /* Header más compacto */
    .header {
        padding: 10px 0;
    }
    
    /* Logo más pequeño para móviles pequeños */
    .logo-img {
        height: 36px;
    }
    
    /* Ocultar texto de logo y mostrar solo imagen en móviles pequeños */
    .logo-text {
        display: none;
    }
    
    /* Iconos de usuario ligeramente más pequeños */
    .user-menu a,
    .user-menu button {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
    
    /* Reducir gap entre elementos */
    .user-menu {
        gap: 10px;
    }
    
    /* Ajuste para el buscador */
    .header-search {
        margin: 0 10px;
    }
    
    .header-search-input {
        height: 40px;
        font-size: 15px;
    }
    
    /* Ajustar posición del menú móvil */
    .mobile-nav {
        top: 72px; /* Ajustar según la nueva altura del header */
    }
}

/* Para alineación de elementos en el header */
@media (max-width: 768px) {
    /* Estructura flex para mejorar alineación */
    .header .container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: nowrap;
    }
    
    /* Logo y marca */
    .brand {
        display: flex;
        align-items: center;
    }
    
    /* Para cuando el header tiene buscador y otros elementos */
    .header-with-search .container {
        flex-wrap: wrap;
    }
    
    .header-top {
        display: flex;
        width: 100%;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .header-bottom {
        width: 100%;
    }
    
    /* Si necesitas un layout de 3 columnas */
    .header-three-col .container {
        grid-template-columns: auto 1fr auto;
        display: grid;
        gap: 15px;
        align-items: center;
    }
}
```
/* ======================================
   ESTILOS RESPONSIVOS PARA MÓVILES PEQUEÑOS
   ====================================== */
@media (max-width: 576px) {
    /* Header */
    .header .container {
        padding-left: 15px;
        padding-right: 15px;
    }

    /* Hero Banner */
    .hero-banner {
        height: 300px;
    }

    .hero-content h1 {
        font-size: 32px;
    }

    .hero-content p {
        font-size: 18px;
    }

    /* Admin dropdown */
    .admin-dropdown {
        width: 280px; /* Más ancho para mejor visualización */
        right: -15px;
    }

    /* Productos - cambiamos a 1 por fila para mejor visualización */
    .products-container .col-md-3 {
        width: 100%;
        padding-left: 10px;
        padding-right: 10px;
    }

    .product-card {
        margin-bottom: 20px;
    }

    .product-image-container {
        height: 220px; /* Imágenes más grandes */
    }

    .product-details {
        padding: 15px 12px;
    }

    .product-title {
        font-size: 18px;
        height: auto;
        margin-bottom: 8px;
        -webkit-line-clamp: 2;
    }

    .product-price {
        font-size: 20px;
        margin-bottom: 10px;
    }

    /* Mejorar experiencia de filtros */
    .search-filter-container .row {
        margin-left: -10px;
        margin-right: -10px;
    }

    .search-filter-container [class*="col-"] {
        padding-left: 10px;
        padding-right: 10px;
    }

    /* Optimizar visualización de búsqueda en móvil */
    .search-filter-container .col-md-4,
    .search-filter-container .col-md-2 {
        width: 100%;
    }

    /* Hacer que los botones sean más accesibles */
    .search-btn, .filter-btn {
        margin-top: 8px;
        padding: 14px 18px;
        width: 100%;
    }

    /* Footer */
    .footer [class*="col-"] {
        width: 100%;
        margin-bottom: 30px;
    }
}

/* ======================================
   MEJORAS DE VISUALIZACIÓN ESPECÍFICAS
   ====================================== */

/* Arreglar problema de menú desplegable en móviles */
@media (max-width: 768px) {
    /* Agregar meta viewport para asegurar escala correcta */
    /* (Añadir en el HTML): <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"> */

    .user-menu .admin-dropdown {
        position: fixed;
        top: auto;
        bottom: 90px;
        right: 25px;
        box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.15);
        z-index: 1060;
    }

    /* Mejora accesibilidad tocando botones */
    .admin-dropdown-item {
        padding: 18px; /* Más espacio para mejor click */
        font-size: 18px; /* Texto más grande en el menú */
        min-height: 54px; /* Altura mínima para elementos táctiles */
    }

    /* Hacer los botones de usuario más grandes en móvil */
    .user-menu .btn {
        width: 60px;
        height: 60px;
        font-size: 24px;
    }

    /* Espaciado adecuado entre íconos */
    .user-menu a {
        margin-right: 22px;
    }

    /* Optimizar carrusel en móvil */
    .carousel-inner .row {
        flex-direction: column;
    }

    .carousel-inner .col-md-4 {
        width: 100%;
        margin-bottom: 30px;
    }

    .carousel-product {
        padding: 20px;
    }

    /* Mejorar espacio del contenido */
    .container {
        padding-left: 20px;
        padding-right: 20px;
    }
    
    /* Mejorar tamaños de botones para mejor toque */
    button, 
    .btn,
    a.btn {
        min-height: 54px; /* Altura mínima recomendada para elementos tocables */
    }
    
    /* Añadir un zoom para viewport móvil */
    html {
        -webkit-text-size-adjust: 100%; /* Prevenir ajuste de texto automático */
    }
    
    /* Mejorar área táctil para todos los elementos interactivos */
    input[type="checkbox"], 
    input[type="radio"] {
        width: 24px;
        height: 24px;
    }
    
    /* Añadir espacio adicional entre elementos interactivos */
    .form-group {
        margin-bottom: 20px;
    }
    
    /* Mejorar visualización de listas */
    ul, ol {
        padding-left: 25px;
    }
    
    li {
        margin-bottom: 10px;
    }
}

/* Orientación horizontal en móviles */
@media (max-width: 992px) and (orientation: landscape) {
    body {
        padding-top: 70px;
    }

    .hero-banner {
        height: 260px;
    }

    .floating-menu {
        top: 90px;
    }
    
    /* Ajustes para orientación horizontal */
    .products-container .col-md-3 {
        width: 50%; /* En landscape permitimos 2 productos por fila */
    }
    
    .product-image-container {
        height: 180px;
    }
}
}
</style>
</head>


<!-- HEADER -->
<header id="header1" class="header px-4 py-2">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Logo -->
            <div>
                <img src="Logo/Recurso 5.png" alt="Imprenta Cisneros Logo" class="logo-img">
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

<!-- Floating Menu (New) -->
<div class="floating-menu">
    <div class="container">
        <div class="main-menu">
            <div class="dropdown">
                <button class="btn dropdown-toggle" type="button" id="mainMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bars me-2"></i> 
                </button>
                <ul class="dropdown-menu" aria-labelledby="mainMenuButton">
                <li><a class="dropdown-item" href="#Header2"><i class="fas fa-house me-2"></i> Inicio</a></li>
                <li><a class="dropdown-item" href="#Carrusel"><i class="fas fa-star me-2"></i> Destacados</a></li>
                <li><a class="dropdown-item" href="#Catalogo"><i class="fas fa-boxes me-2"></i> Catálogo</a></li>
                <li><a class="dropdown-item" href="#footer"><i class="fas fa-map-marker-alt me-2"></i> Contacto</a></li>

                </ul>
            </div>
        </div>
    </div>
</div>

<!-- HERO BANNER -->
<div id="Header2" class="hero-banner">
    <img src="aqui/ImpreMex-Mejores-Imprentas-cerca-de-mi-en-Mexico-ImpreMex-com.jpg" alt="Imprenta Cisneros Banner">
    <div class="hero-overlay">
        <div class="hero-content">
            <h1>Imprenta Cisneros</h1>
            <p>Soluciones de impresión profesional para dar vida a tus ideas</p>
            <a href="#Catalogo" class="hero-btn">Ver productos</a>
        </div>
    </div>
</div>


<!-- CAROUSEL - MÁS VENDIDOS -->
<div id="Carrusel" class="container">
    <h3 class="section-title">Productos Destacados</h3>
    <div class="carousel-container">
        <div id="masVendidosCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
    <?php 
    $active = 'active';
    $total_products = count($productos);

    // Recorremos el array de productos para crear las diapositivas
    for ($i = 0; $i < $total_products; $i += 3) {
    ?>
    <div class="carousel-item <?php echo $active; ?>">
        <div class="row">
            <?php 
            // Mostramos hasta 3 productos por diapositiva
            for ($j = $i; $j < $i + 3 && $j < $total_products; $j++) { 
                $producto = $productos[$j];
                ?>
                <div class="col-md-4">
                    <a href="Detalles.php?IDproducto=<?php echo htmlspecialchars($producto['IDproducto']); ?>" class="text-decoration-none">
                        <div class="carousel-product">
                            <!-- Imagen -->
                            <img src="<?php echo htmlspecialchars($producto['Imagen']); ?>" class="d-block mx-auto" alt="<?php echo htmlspecialchars($producto['Nombre']); ?>">
                            <!-- Nombre -->
                            <h5><?php echo htmlspecialchars($producto['Nombre']); ?></h5>
                            <!-- Precio -->
                            <p class="price">$MXN <?php echo number_format($producto['Precio'], 2); ?></p>
                        </div>
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php 
        $active = ''; // Solo la primera diapositiva tiene la clase 'active'
        } 
        ?>
    </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#masVendidosCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#masVendidosCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>
</div>

<!-- BÚSQUEDA Y FILTROS -->
<div class="container">
    <div class="search-filter-container">
        <form method="get" class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="busqueda" id="busqueda" class="form-control search-input" placeholder="Buscar producto..." value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn search-btn w-100">Buscar</button>
            </div>
            <div class="col-md-2">
                <select name="genero" id="genero" class="form-select filter-select">
                    <option value="">Género</option>
                    <option value="Mujer" <?php if ($filtroGenero == 'Mujer') echo 'selected'; ?>>Mujer</option>
                    <option value="Hombre" <?php if ($filtroGenero == 'Hombre') echo 'selected'; ?>>Hombre</option>
                    <option value="Unisex" <?php if ($filtroGenero == 'Unisex') echo 'selected'; ?>>Unisex</option>
                    <option value="No aplica" <?php if ($filtroGenero == 'No aplica') echo 'selected'; ?>>No aplica</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="tipo" id="tipo" class="form-select filter-select">
                    <option value="">Tipo</option>
                    <?php while ($t = $tipos->fetch_assoc()): ?>
                        <option value="<?php echo $t['Tipo']; ?>" <?php if ($filtroTipo == $t['Tipo']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($t['Tipo']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn filter-btn w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- CATÁLOGO DE PRODUCTOS -->
<div id="Catalogo" class="container">
    <h2 class="section-title">Nuestros Productos</h2>
    <div class="products-container">
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="product-card">
                        <!-- Ícono favorito -->
                        <div class="favorito <?php echo isset($_SESSION['usuario_id']) && $conn->query("SELECT 1 FROM listadeseos WHERE IDproducto = {$row['IDproducto']} AND IDusuario = {$_SESSION['usuario_id']}")->num_rows ? 'activo' : ''; ?>"
                            onclick="toggleFavorito(this)"
                            data-id="<?php echo $row['IDproducto']; ?>"
                            data-precio="<?php echo $row['Precio']; ?>">
                            <i class="fas fa-heart"></i>
                        </div>
                        
                        <!-- Enlace a detalles -->
                        <a href="Detalles.php?IDproducto=<?php echo $row['IDproducto']; ?>" class="text-decoration-none">

                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars($row['Imagen']); ?>" alt="<?php echo htmlspecialchars($row['Nombre']); ?>" class="product-image">
                            </div>
                            <div class="product-details">
                                <h5 class="product-title"><?php echo htmlspecialchars($row['Nombre']); ?></h5>
                                <p class="product-price">$MXN <?php echo number_format($row['Precio'], 2); ?></p>
                                <div class="color-options">
                                    <?php
                                    $colores = explode(',', $row['Colores']);
                                    foreach (array_unique($colores) as $color) {
                                        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                                            echo "<span class='color-circle' style='background-color: {$color};'></span>";
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Paginación -->
        <div class="pagination-container text-center">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>" class="btn pagination-btn <?php if ($i == $paginaActual) echo 'active'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- SOBRE NOSOTROS -->
<div class="container">
    <div class="about-us-container">
        <div class="row">
            <div class="col-lg-6">
                <div class="about-us-content">
                    <h2 class="about-us-title">¿Quiénes Somos?</h2>
                    <p class="about-us-text">En <span class="about-us-highlight">Imprenta Cisneros</span> no solo imprimimos: creamos resultados en confianza, calidad y en resultados que hablan por sí solos. Creemos que una buena impresión puede abrir muchas puertas, por eso cuidamos cada detalle: desde el diseño hasta el acabado final.</p>
                    <p class="about-us-text">Usamos tecnología de vanguardia y materiales ecológicos, porque queremos que tu marca destaque sin descuidar el planeta.</p>
                    <p class="about-us-text">Nos especializamos en soluciones de impresión para todo tipo de negocios, instituciones y emprendedores. Con nosotros puedes contar con volantes, trípticos, tarjetas de presentación, reconocimientos, lonas, camisas personalizadas y muchas cosas más.</p>
                    <p class="about-us-text">En <span class="about-us-highlight">Imprenta Cisneros</span> llevamos más de <span class="about-us-highlight">30 años</span> imprimiendo ideas desde <span class="about-us-highlight">Ciudad Juárez, Chihuahua</span>. Trabajamos para transformar cada proyecto en algo único, profesional y memorable.</p>
                    <p class="about-us-text about-us-highlight">Haz que tu proyecto cobre vida. Trabaja con los que sí saben. Tenemos todo lo que necesitas para que tu marca destaque.</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-us-image">
                    <img src="aqui/763.jpg" alt="Imprenta Cisneros" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer id="footer" class="footer">
    <div class="container">
        <div class="row">
            <!-- Mapa -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="footer-map">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3481.5428041777165!2d-106.44525842461574!3d31.694898474155824!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86e75e832a48054d%3A0x9b72f1ea4cf80eb1!2sC.%20Minatitl%C3%A1n%205450%2C%20Acacias%2C%2032630%20Ju%C3%A1rez%2C%20Chih.!5e0!3m2!1ses!2smx!4v1715637204556!5m2!1ses!2smx" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

            <!-- Información -->
            <div class="col-lg-8">
                <div class="row">
                    <!-- Ubicación -->
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title">Ubicación</h5>
                        <div class="footer-info">
                            <p class="mb-0">C. Minatitlán 5450, Acacias, 32630</p>
                            <p>Juárez, Chihuahua</p>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title">Contáctanos</h5>
                        <div class="footer-info">
                            <p class="mb-1"><i class="fas fa-phone me-2"></i> +52 (656) 791 8482</p>
                            <p class="mb-1"><i class="fas fa-phone me-2"></i> +52 (656) 791 8481</p>
                            <p><i class="fas fa-phone me-2"></i> +52 (656) 429 0256</p>
                        </div>
                    </div>

                    <!-- Horario -->
                    <div class="col-md-4">
                        <h5 class="footer-title">Horario</h5>
                        <div class="footer-info">
                            <p class="mb-2"><i class="fas fa-clock me-2"></i> <strong>Lun - Vie</strong><br>8:00 - 18:00</p>
                            <p><i class="fas fa-clock me-2"></i> <strong>Sáb - Dom</strong><br>Cerrado</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Créditos -->
        <div class="footer-credits">
            <div class="d-flex justify-content-between flex-column flex-md-row text-center text-md-start">
                <span>Sitio realizado por Huelicatl</span>
                <span>© 2025 IMPRENTA CISNEROS S. DE R.L. DE C.V</span>
            </div>
        </div>
    </div>
</footer>





<!--CODIGO JAVASCRIPTS-->
<script>
function toggleFavorito(element) {
    element.classList.toggle('activo');
}
document.getElementById('userToggle').addEventListener('click', function (e) {
    const menu = document.getElementById('userMenu');
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    e.stopPropagation();
});
document.addEventListener('click', function (event) {
    const toggle = document.getElementById('userToggle');
    const menu = document.getElementById('userMenu');
    if (!toggle.contains(event.target) && !menu.contains(event.target)) {
        menu.style.display = 'none';
    }
});
</script>

<script>
function toggleFavorito(element) {
    const productoId = element.dataset.id;
    const precio = element.dataset.precio;

    fetch('toggle_deseo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `productoId=${productoId}&precio=${precio}`
    })
    .then(res => {
        if (res.status === 401) {
            alert("Debes iniciar sesión para usar la lista de deseos.");
            return;
        }
        return res.json();
    })
    .then(data => {
        if (data.accion === 'agregado') {
            element.classList.add('activo');
        } else if (data.accion === 'eliminado') {
            element.classList.remove('activo');
        }
    })
    .catch(err => {
        console.error("Error al procesar el favorito:", err);
    });
}
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
