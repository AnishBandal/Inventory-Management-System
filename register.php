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

// Function to securely hash the password using bcrypt
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST["firstName"] ?? '';
    $lastname = $_POST["lastName"] ?? '';
    $email = $_POST["email"] ?? '';
    $phoneno = $_POST["phoneNumber"] ?? '';
    $password = $_POST["password"] ?? '';
    
    // Validate inputs
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT User_Id FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        exit;
    }
    $stmt->close();

    // Hash the password
    $hashedPassword = hashPassword($password);

    // Prepare and bind the SQL statement
    $stmt = $conn->prepare("INSERT INTO users (FirstName, LastName, Email, Phone_No, Password, Role) VALUES (?, ?, ?, ?, ?, 'Admin')");
    $stmt->bind_param('sssss', $firstname, $lastname, $email, $phoneno, $hashedPassword);

    // Execute the statement
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        
        // Set session variables
        $_SESSION['User_Id'] = $user_id;
        $_SESSION['Email'] = $email;
        $_SESSION['Role'] = 'Admin';
        
        echo json_encode([
            'status' => 'success',
            'redirect' => 'dashboard.php'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>