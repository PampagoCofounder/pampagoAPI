<?php
require_once __DIR__ . "/../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    $id_factura = $data['id_factura'];
    $monto = $data['monto'];

    $conn->query("INSERT INTO pagos (id_factura, fecha, monto, metodo)
                  VALUES ($id_factura, NOW(), $monto, 'efectivo')");

    // verificar si está pagada
    $res = $conn->query("
        SELECT f.total, SUM(p.monto) as pagado
        FROM facturas f
        LEFT JOIN pagos p ON f.id_factura = p.id_factura
        WHERE f.id_factura = $id_factura
        GROUP BY f.id_factura
    ");

    $row = $res->fetch_assoc();

    if ($row['pagado'] >= $row['total']) {
        $conn->query("UPDATE facturas SET estado='pagada' WHERE id_factura = $id_factura");
    }

    echo json_encode(["ok" => true]);
}