<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";


$decoded = validarJWT();
$db = (new Database())->connect();

$mensaje = strtolower($_POST['mensaje'] ?? '');
$respuesta = "No entendí la consulta";

// 🟢 SALUDOS
if (strpos($mensaje, "hola") !== false || strpos($mensaje, "buenos dias") !== false) {
    $respuesta = "¡Hola! Buenos días 👋 ¿En qué puedo ayudarte?";
}

// 🟢 COMO ESTAS
elseif (strpos($mensaje, "como estas") !== false) {
    $respuesta = "Estoy funcionando correctamente 😄 ¿Necesitás información de proveedores o productos?";
}

// 🟢 NOMBRE DEL BOT
elseif (strpos($mensaje, "nombre") !== false) {
    $respuesta = "Soy el asistente de proveedores y productos de tu sistema.";
}

// 🟢 QUIEN TE CREO
elseif (strpos($mensaje, "quien te creo") !== false || strpos($mensaje, "quien te hizo") !== false) {
    $respuesta = "Fui creado por un desarrollador para gestionar proveedores y productos 😉";
}


/* RECUERDOS DE KAREN */
elseif(strpos($mensaje,"karen") !== false || strpos($mensaje,"karen")){
   $respuesta = "Hola señora, soy Karen cuidela a mi rulos por mi. Aunque me duela pronto estaremos";
}

elseif(strpos($mensaje,"Gracias") !== false || strpos($mensaje,"Gracias karen")){
    $respuesta = "De nada señora, le mando saludos a tonton y tintin y a mito que no sea pajero";
}




// 🔵 CUANTOS PRODUCTOS
elseif (strpos($mensaje, "cuantos productos") !== false) {
    $query = $conn->query("SELECT COUNT(*) as total FROM productos");
    $data = $query->fetch_assoc();
    $respuesta = "Hay " . $data['total'] . " productos registrados.";
}

// 🔵 LISTAR PROVEEDORES
elseif (strpos($mensaje, "proveedores") !== false) {
    $query = $db->query("SELECT nombre FROM proveedores");
    $lista = [];

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $lista[] = $row['nombre'];
    }

    $respuesta = "Proveedores disponibles: " . implode(", ", $lista);
}

// 🔵 PRODUCTOS POR PROVEEDOR
// 🔵 LISTAR PROVEEDORES
elseif (strpos($mensaje, "productos") !== false) {
    $query = $db->query("SELECT nombre_producto FROM producto");
    $lista = [];

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $lista[] = $row['nombre_producto'];
    }

    $respuesta = "Productos disponibles: " . implode(", ", $lista);
}

echo json_encode(["respuesta" => $respuesta]);