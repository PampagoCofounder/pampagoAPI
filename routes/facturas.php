<?php
require_once __DIR__ . "/../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    $id_venta = $data['id_venta'];

    // obtener total de la venta
    $res = $conn->query("SELECT total FROM ventas WHERE id_venta = $id_venta");
    $venta = $res->fetch_assoc();

    $total = $venta['total'];

    // crear factura
    $conn->query("INSERT INTO facturas (id_venta, numero, tipo, fecha, total)
                  VALUES ($id_venta, 1, 'B', NOW(), $total)");

    // actualizar estado
    $conn->query("UPDATE ventas SET estado = 'facturada' WHERE id_venta = $id_venta");

    echo json_encode(["ok" => true]);
}