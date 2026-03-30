<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";

$decoded = validarJWT();
$db = (new Database())->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // 📥 OBTENER CATEGORIAS
    case "GET":

        $stmt = $db->prepare("
            SELECT id_categoria, nombre 
            FROM categoria
        ");

        $stmt->execute();

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // ➕ CREAR CATEGORIA
    case "POST":

        $data = json_decode(file_get_contents("php://input"), true);

        $nombre = $data['nombre'] ?? null;

        if (!$nombre) {
            echo json_encode(["error" => "Nombre requerido"]);
            exit;
        }

        $stmt = $db->prepare("
            INSERT INTO categoria (nombre)
            VALUES (?)
        ");

        $stmt->execute([$nombre]);

        echo json_encode([
            "ok" => true,
            "id_categoria" => $db->lastInsertId()
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}