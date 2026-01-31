<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'mysql');
define('DB_NAME', 'cineverse');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

function generateBookingCode() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['username'] === 'admin';
}

function getCurrentUser() {
    if (isLoggedIn()) {
        $conn = getDBConnection();
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        
        if ($user_data) {
            return [
                'id' => $user_data['id'],
                'username' => $user_data['username'],
                'full_name' => $user_data['full_name'] ?? '',
                'email' => $user_data['email'],
                'phone' => $user_data['phone'] ?? ''
            ];
        }
    }
    return null;
}
?>
