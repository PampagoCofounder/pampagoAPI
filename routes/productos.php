<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";


$decoded = validarJWT();
$db = (new Database())->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // 📦 OBTENER PRODUCTOS
    case "GET":
        // opcional: ?id_empresa=1
        $id_cliente = $_GET['id_cliente'] ?? null;

        if ($id_cliente) {
            $stmt = $db->prepare("SELECT p.*, s.nombre AS subcategoria FROM producto p LEFT JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria WHERE id_cliente = ?");
            $stmt->execute([$id_cliente]);
        } else {
            $stmt = $db->prepare("SELECT p.*, s.nombre AS subcategoria, c.nombre AS categoria FROM producto p LEFT JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria LEFT JOIN categoria c ON s.id_categoria = c.id_categoria");
            $stmt->execute();
        }


        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;



    // ➕ CREAR PRODUCTO
    case "POST":

        $data = json_decode(file_get_contents("php://input"), true);
   

        $nombre_producto = $data['nombre_producto'] ?? null;
        $precio_producto = $data['precio_producto'] ?? null;
        $stock_producto = $data['stock'] ?? null;
        $costo = $data['costo'] ?? null;
        $id_subcategoria = $data['id_subcategoria'] ?? null;

        if(
            $nombre_producto === null ||
            $precio_producto === null || 
            $stock_producto === null || 
            $costo === null || 
            $id_subcategoria === null 

        ){
            echo json_encode([
                "ok" => false,
                "error" => "Datos incompletos"
             ]);
            exit; 
        };

        //query segura
        $stmt = $db->prepare("
         INSERT INTO producto (nombre_producto,precio_producto,stock,costo,id_subcategoria) 
         VALUES (?,?,?,?,?)
        ");


        $stmt->execute([$nombre_producto,$precio_producto,$stock_producto,$costo,$id_subcategoria]);

   
        echo json_encode(["ok" => true]);
        break;

    // ✏️ ACTUALIZAR PRODUCTO
    case "PUT":

        $data = json_decode(file_get_contents("php://input"), true);

        $id_producto = $data['id_producto'];
        $nombre_producto = $data['nombre_producto'];
        $precio_producto = $data['precio_producto'];


        $sql = "UPDATE productos 
                SET nombre='$nombre_producto', precio=$precio_producto,
                WHERE id_producto=$id_producto";

        $conn->query($sql);

        echo json_encode(["ok" => true]);
        break;

    // ❌ ELIMINAR PRODUCTO
    case "DELETE":

        //$data = json_decode(file_get_contents("php://input"), true);

        $id_producto = $_GET['id_producto'] ?? null;

        if(!$id_producto){
          echo json_encode([
              "ok" => false,
              "error" => "ID requerido"
          ]);
          exit;
        }

        $stmt = $db->prepare("DELETE FROM producto WHERE id_producto=?");
        $stmt->execute([$id_producto]);

        echo json_encode([
            "ok" => true,
            "message" => "Producto eliminado"
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}
