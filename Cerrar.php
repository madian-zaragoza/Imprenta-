<?php
session_start();
session_unset();    // Limpia todas las variables de sesión
session_destroy();  // Destruye la sesión

// Redirige de vuelta al catálogo
header("Location: Imprenta Cisneros.php"); // Cambia a tu archivo del catálogo si tiene otro nombre
exit();
