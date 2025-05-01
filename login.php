<?php
// Secure cookie settings
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to handle login failure
function handleLoginFailure() {
    echo '<script>alert("Login Failure!");</script>';
    echo '<script>window.location.href = "http://localhost/Inventory%20Management%20System/login.html";</script>';
    exit;
}

// Retrieve and sanitize inputs
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("Invalid Email Format!");</script>';
    exit;
}

// Check if too many login attempts
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    echo '<script>alert("Too many failed login attempts. Please try again later.");</script>';
    echo '<script>window.location.href = "http://localhost/Inventory%20Management%20System/login.html";</script>';
    exit;
}

// Prepare and bind SQL
$stmt = $conn->prepare("SELECT password, User_Id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashedPass = $row['password'];
    $User_Id = $row['User_Id'];

    if (password_verify($password, $hashedPass)) {
        session_regenerate_id(true); // Regenerate session ID

        $_SESSION['User_Id'] = $User_Id;
        $_SESSION['Email'] = $email;
        unset($_SESSION['login_attempts']); // Reset login attempts on success

        echo '<script>alert("Login Successful!");</script>';
        echo '<script>window.location.href = "http://localhost/Inventory%20Management%20System/dashboard.php";</script>';
        exit;
    } else {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        handleLoginFailure();
    }
} else {
    handleLoginFailure();
}

$stmt->close();
$conn->close();
?>
