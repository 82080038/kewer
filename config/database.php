<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kewer');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Global function for query
function query($sql, $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    
    if (strpos($sql, 'SELECT') === 0) {
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
    
    return $stmt->affected_rows;
}
?>
