<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Retrieve the entered email and password
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Prepare and bind the SQL statement
$stmt = $conn->prepare("SELECT User_Id, Password, Role, Status FROM users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    if (password_verify($password, $row['Password'])) {
        if ($row['Status'] === 'Suspended') {
            echo json_encode(['status' => 'error', 'message' => 'Your account is suspended']);
            exit;
        }
        
        // Set session variables
        $_SESSION['User_Id'] = $row['User_Id'];
        $_SESSION['Email'] = $email;
        $_SESSION['Role'] = $row['Role'];
        
        echo json_encode([
            'status' => 'success',
            'redirect' => 'dashboard.php'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}

$stmt->close();
$conn->close();
?>