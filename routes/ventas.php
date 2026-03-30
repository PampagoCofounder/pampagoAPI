<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";

// Validar JWT
$decoded = validarJWT();
$id_empresa = $decoded->data->id_empresa ?? null;

if (!$id_empresa) {
    echo json_encode(["ok" => false, "error" => "Empresa no encontrada en token"]);
    exit;
}

$db = (new Database())->connect();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        // opcional: ?id_cliente=1
        $id_cliente = $_GET['id_cliente'] ?? null;

        if ($id_cliente) {
            $stmt = $db->prepare("SELECT * FROM venta WHERE id_cliente = ?");
            $stmt->execute([$id_cliente]);
        } else {
            $stmt = $db->prepare("SELECT * FROM venta");
            $stmt->execute();
        }

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // ➕ CREAR VENTA + DETALLE
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);

        $id_cliente = (int)($data['id_cliente'] ?? 0);
        $productos = $data['productos'] ?? [];

        if (!$id_cliente) {
            echo json_encode(["ok" => false, "error" => "Cliente no especificado"]);
            exit;
        }

        if (!is_array($productos) || count($productos) === 0) {
            echo json_encode(["ok" => false, "error" => "No hay productos"]);
            exit;
        }

        // Verificar que el cliente exista
        $stmtCliente = $db->prepare("SELECT * FROM cliente WHERE id_cliente = ? AND id_empresa = ?");
        $stmtCliente->execute([$id_cliente, $id_empresa]);
        $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            echo json_encode(["ok" => false, "error" => "Cliente no existe en esta empresa"]);
            exit;
        }

        try {
            $db->beginTransaction();

            // Insertar venta
            $stmtVenta = $db->prepare("
                INSERT INTO venta (id_cliente, id_empresa, fecha, total, estado)
                VALUES (?, ?, NOW(), 0, 'pendiente')
            ");
            $stmtVenta->execute([$id_cliente, $id_empresa]);
            $id_venta = $db->lastInsertId();

            $total = 0;

            // Insertar detalle de venta
            $stmtDetalle = $db->prepare("
                INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($productos as $p) {
                $id_producto = $p['id_producto'] ?? null;
                $cantidad = $p['cantidad'] ?? null;

                if (!$id_producto || !$cantidad) continue;

                // Verificar que el producto exista y sea de esta empresa
                $stmtProd = $db->prepare("SELECT precio_producto FROM producto WHERE id_producto = ?");
                $stmtProd->execute([$id_producto]);
                $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);

                if (!$prod) continue; // producto no existe o no pertenece a esta empresa

                $precio = $prod['precio_producto'];
                $stmtDetalle->execute([$id_venta, $id_producto, $cantidad, $precio]);

                $total += $precio * $cantidad;
            }

            // Actualizar total de venta
            $stmtUpdate = $db->prepare("UPDATE venta SET total = ? WHERE id_venta = ?");
            $stmtUpdate->execute([$total, $id_venta]);

            $db->commit();

            echo json_encode([
                "ok" => true,
                "id_venta" => $id_venta,
                "total" => $total
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode([
                "ok" => false,
                "error" => "Error al crear la venta: " . $e->getMessage()
            ]);
        }

        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}
