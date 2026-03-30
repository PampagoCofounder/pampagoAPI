<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";

$decoded = validarJWT();
$db = (new Database())->connect();

$stmt = $db->prepare("SELECT * FROM empleados WHERE empresa_id=?");
$stmt->execute([$decoded->data->id_empresa]);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($empleados);
?>