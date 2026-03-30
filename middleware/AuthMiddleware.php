<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validarJWT() {
    $headers = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$headers) {
        http_response_code(401);
        echo json_encode(["error" => "Token requerido"]);
        exit();
    }

    $token = str_replace("Bearer ", "", $headers);
    $key = "mi_clave_super_secreta_de_32_caracteres_minimo_2026";

    try {
        return JWT::decode($token, new Key($key, "HS256"));
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido"]);
        exit();
    }
}