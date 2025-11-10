<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, X-API-Key");

require_once "config/Database.php";
require_once "config/Auth.php";
require_once "models/EmployeeModel.php";

$db = (new Database())->connect();
$auth = new Auth($db);
$auth->checkKey();

$employeeModel = new EmployeeModel($db);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$resource = $path[1] ?? null;
$id = $path[2] ?? null;

if ($resource !== 'employees') {
    http_response_code(404);
    echo json_encode(["error" => "Resource not found"]);
    exit;
}

switch ($requestMethod) {

    case 'GET':
        try {
            if ($id) {
                $data = $employeeModel->getById($id);
                if ($data) {
                    http_response_code(200);
                    echo json_encode($data);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Employee not found"]);
                }
            } else {
                $data = $employeeModel->getAll();
                http_response_code(200);
                echo json_encode($data);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Internal server error"]);
        }
        break;

    case 'POST':
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input || !isset($input['first_name'], $input['last_name'], $input['position'], $input['salary'])) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid input"]);
                exit;
            }
            $employeeModel->create($input);
            http_response_code(201);
            echo json_encode(["message" => "Employee created"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Internal server error"]);
        }
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Missing employee ID"]);
            exit;
        }

        $existing = $employeeModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["error" => "Employee not found"]);
            exit;
        }

        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input || !is_array($input)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input"]);
            exit;
        }

        $dataToUpdate = [
            'first_name' => $input['first_name'] ?? $existing['first_name'],
            'last_name'  => $input['last_name'] ?? $existing['last_name'],
            'position'   => $input['position'] ?? $existing['position'],
            'salary'     => $input['salary'] ?? $existing['salary']
        ];

        try {
            $employeeModel->update($id, $dataToUpdate);
            http_response_code(200);
            echo json_encode(["message" => "Employee updated"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Internal server error"]);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Missing employee ID"]);
            exit;
        }

        $existing = $employeeModel->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["error" => "Employee not found"]);
            exit;
        }

        try {
            $employeeModel->delete($id);
            http_response_code(200);
            echo json_encode(["message" => "Employee deleted"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Internal server error"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
}
