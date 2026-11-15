<?php
    // Set response headers for JSON REST API and CORS
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Handle browser OPTIONS requests
    if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
        http_response_code(200);
        exit;
    }

    require_once __DIR__ . '/controllers/AuthController.php';

    // Allow only POST requests for authentication actions
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed. Use POST.']);
        exit;
    }

    // Read and decode raw JSON input from request body
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if(!is_array($data)){
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON payload']);
        exit;
    }

    // Route request based on ?action= query parameter
    $action = $_GET['action'] ?? '';
    $authController = new AuthController();
    
    switch ($action){
        case 'register';
            $authController->register($data);
            break;

        case 'login';
            $authController->login($data);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing action. Use ?action=register or ?action=login']);
            break;
    }


?>