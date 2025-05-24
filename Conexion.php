<?php
$host = "fdb1028.awardspace.net";       // Nombre del servidor (normalmente es "localhost")
$usuario = "4635048_imprenta";         // Tu usuario de la base de datos (por defecto en XAMPP es "root")
$contrasena = "Imprenta001-";          // Tu contraseña (en XAMPP normalmente está vacía)
$baseDeDatos = "4635048_imprenta"; // Cambia esto por el nombre de tu base de datos

// Crear la conexión
$conn = new mysqli($host, $usuario, $contrasena, $baseDeDatos);

// Verificar si la conexión funciona
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Si llega hasta aquí, la conexión fue exitosa
// Puedes dejar esto o quitarlo
// echo "Conexión exitosa";
?>
