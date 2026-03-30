<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";

$decoded = validarJWT();
$db = (new Database())->connect();

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id=?");
$stmt->execute([$decoded->data->id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([$user]);
?>