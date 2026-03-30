<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";

$decoded = validarJWT();
$db = (new Database())->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // 📥 OBTENER CLIENTES (por empresa)
    case "GET":

        // opcional: ?id_empresa=1
        $id_empresa = $_GET['id_empresa'] ?? null;

        if ($id_empresa) {
            $stmt = $db->prepare("SELECT * FROM cliente WHERE id_empresa = ?");
            $stmt->execute([$id_empresa]);
        } else {
            $stmt = $db->prepare("SELECT * FROM cliente");
            $stmt->execute();
        }


        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // ➕ CREAR CLIENTE
    case "POST":

        $data = json_decode(file_get_contents("php://input"), true);

        $id_empresa = $data['id_empresa'];
        $nombre = $data['nombre_cliente'];
        $cuit = $data['cuit_cliente'];
        $email = $data['email_cliente'];

        $stmt = $conn->prepare("
            INSERT INTO cliente (id_empresa, nombre_cliente, cuit_cliente, email_cliente)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param("isss", $id_empresa, $nombre, $cuit, $email);
        $stmt->execute();

        echo json_encode([
            "ok" => true,
            "id_cliente" => $conn->insert_id
        ]);
        break;

    // ✏️ ACTUALIZAR CLIENTE
    case "PUT":

        $data = json_decode(file_get_contents("php://input"), true);

        $id = $data['id_cliente'];
        $nombre = $data['nombre'];
        $cuit = $data['cuit'];
        $email = $data['email'];

        $stmt = $conn->prepare("
            UPDATE cliente 
            SET nombre = ?, cuit = ?, email = ?
            WHERE id_cliente = ?
        ");

        $stmt->bind_param("sssi", $nombre, $cuit, $email, $id);
        $stmt->execute();

        echo json_encode(["ok" => true]);
        break;

    // ❌ ELIMINAR CLIENTE
    case "DELETE":

        $data = json_decode(file_get_contents("php://input"), true);

        $id = $data['id_cliente'];

        $stmt = $conn->prepare("DELETE FROM cliente WHERE id_cliente = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["ok" => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}