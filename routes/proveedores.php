<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";

$decoded = validarJWT();
$db = (new Database())->connect();

$stmt = $db->prepare("SELECT * FROM proveedores WHERE id_empresa=?");
$stmt->execute([$decoded->data->id_empresa]);
$empresa = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($empresa);
?>