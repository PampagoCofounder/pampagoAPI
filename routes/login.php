<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;

$data = json_decode(file_get_contents("php://input"));
if (!$data || !isset($data->user) || !isset($data->pass)) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$db = (new Database())->connect();

$stmt = $db->prepare("SELECT * FROM usuarios WHERE name_users=?");
$stmt->execute([$data->user]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($data->pass, $user["pass_users"])) {
    http_response_code(401);
    echo json_encode(["error" => "Credenciales inválidas"]);
    exit();
}

$key = "mi_clave_super_secreta_de_32_caracteres_minimo_2026";
$payload = [
    "iat" => time(),
    "exp" => time() + 3600,
    "data" => [
        "id" => $user["id"],
        "user" => $user["name_users"],
        "id_empresa" => $user["id_empresa"]
    ]
];

$jwt = JWT::encode($payload, $key, "HS256");

echo json_encode(["token" => $jwt]);
