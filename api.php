<?php
header("Access-Control-Allow-Origin: http://localhost:3000"); // Permite solicitudes desde React
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos HTTP permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Encabezados permitidos
header("Access-Control-Allow-Credentials: true"); // Habilita el envío de cookies si es necesario

include 'db.php';

// Detectar el método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Obtener todos los contactos
        $sql = "SELECT * FROM contacts";
        $result = $conn->query($sql);
        $contacts = [];
        while ($row = $result->fetch_assoc()) {
            $contacts[] = $row;
        }
        echo json_encode($contacts);
        break;

    case 'POST':
        // Agregar un nuevo contacto
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $address = $data['address'] ?? '';

        // Validación básica
        if (empty($name) || empty($email)) {
            echo json_encode(["error" => "Name and Email are required"]);
            http_response_code(400);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO contacts (name, email, phone, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $address);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Contact added"]);
        } else {
            echo json_encode(["error" => "Failed to add contact"]);
            http_response_code(500);
        }
        $stmt->close();
        break;

    case 'PUT':
        // Actualizar un contacto existente
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $address = $data['address'] ?? '';

        if (empty($id) || empty($name) || empty($email)) {
            echo json_encode(["error" => "ID, Name, and Email are required"]);
            http_response_code(400);
            break;
        }

        $stmt = $conn->prepare("UPDATE contacts SET name=?, email=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $address, $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Contact updated"]);
        } else {
            echo json_encode(["error" => "Failed to update contact"]);
            http_response_code(500);
        }
        $stmt->close();
        break;

        case 'DELETE':
            // Leer y decodificar el cuerpo de la solicitud DELETE
            $input = json_decode(file_get_contents("php://input"), true);
            $id = $input['id'] ?? null;
        
            if (empty($id)) {
                echo json_encode(["error" => "ID is required"]);
                http_response_code(400);
                break;
            }
        
            $stmt = $conn->prepare("DELETE FROM contacts WHERE id=?");
            $stmt->bind_param("i", $id);
        
            if ($stmt->execute()) {
                echo json_encode(["message" => "Contact deleted"]);
            } else {
                echo json_encode(["error" => "Failed to delete contact"]);
                http_response_code(500);
            }
            $stmt->close();
            break;
        
        

    default:
        echo json_encode(["error" => "Invalid request method"]);
        http_response_code(405);
        break;
}

$conn->close();
?>
