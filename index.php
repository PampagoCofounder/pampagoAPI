<?php
header("Content-Type: application/json");
//header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Origin: https://pampago.site/");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . "/vendor/autoload.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = "/pampagoAPI/";
$route = str_replace($base, "", $uri);
$route = trim($route, "/");

switch ($route) {
    case "login":
        require_once __DIR__ . "/routes/login.php";
        break;
    case "usuarios":
        require_once __DIR__ . "/routes/usuarios.php";
        break;
    case "empresa":
        require_once __DIR__ . "/routes/empresa.php";
        break;
    case "proveedores":
        require_once __DIR__ . "/routes/proveedores.php";
        break;
    case "empleados":
        require_once __DIR__ . "/routes/empleados.php";
        break;
    case "ventas":
        require_once __DIR__ . "/routes/ventas.php";
        break;
    
    case "facturas":
        require_once __DIR__ . "/routes/facturas.php";
        break;
    
    case "pagos":
        require_once __DIR__ . "/routes/pagos.php";
        break;
    
    case "productos":
        require_once __DIR__ ."/routes/productos.php";
        break;

    case "clientes":
        require_once __DIR__ . "/routes/clientes.php";
        break;
    
    case "subcategorias":
        require_once __DIR__ . "/routes/subcategoria.php";
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada", "ruta" => $route]);
}
