<?php
    require_once __DIR__ . '/../config/Database.php';

    class AuthController{
        private PDO $db;
        
        //Database connection
        public function __construct() {
            $this->db = Database::getInstance()->getConnection();
        }

        //New user registration
        public function register(array $data): void {
            //Validate required input fields
            if(empty($data['email']) || empty($data['password']) || empty($data['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Required fields: email, password, name']);
                return;
            }

            //Validate email address
            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email address format']);
                return;
            }

            // Check if a user with this email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $data['email']]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['error' => 'A user with this email already exists']);
                return;
            }

            // Hash the password securely and insert the new user into the database
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("INSERT INTO users (email, password_hash, name) VALUES (:email, :password_hash, :name)");
            $stmt ->execute([
                'email' => $data['email'],
                'password_hash' => $hashedPassword,
                'name' => htmlspecialchars(strip_tags($data['name']))
            ]);

            // Return success code when account is created
            http_response_code(201);
            echo json_encode([
                'message' => 'Account created successfully',
                'user_id' => $this->db->lastInsertId()
            ]);

            }
        
            
            // Handle existing user authentication
        public function login(array $data): void{
            if (empty($data['email']) || empty($data['password'])){
                http_response_code(400);
                echo json_encode(['error' => 'Required fields: email, password']);
                return;
            }

            // Get user data by email
            $stmt = $this->db->prepare("SELECT id, email, password_hash, name FROM users WHERE email = :email");
            $stmt->execute(['email' => $data['email']]);
            $user = $stmt->fetch();

            // Verify if user exists and password hash match
            if(!$user || !password_verify($data['password'], $user['password_hash'])){
                http_response_code(401);
                echo json_encode(['error' => 'Invalid email or password']);
                return;
            }


            // Start PHP session and store authenticated user ID
            if(session_status() === PHP_SESSION_NONE){
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Return success response with user profile data
            http_response_code(200);
            echo json_encode([
                'message' => 'Logged in successfully',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ]
            ]);
        }

    }
?>  