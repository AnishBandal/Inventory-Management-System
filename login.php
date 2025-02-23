<?php
session_start();

// Database configuration and connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to securely hash the password using bcrypt
function hashPassword($password)
{
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    return $hashedPassword;
}

// Retrieve the entered email and password
$email = $_POST['email'];
$password = $_POST['password'];

// Prepare and bind the SQL statement
$stmt = $conn->prepare("SELECT password, User_Id FROM registration_details WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashedPass = $row['password'];
    $User_Id = $row['User_Id'];

    if (password_verify($password, $hashedPass)) {
        $_SESSION['User_Id'] = $User_Id; // Store the User_Id in a session variable
        $_SESSION['Email'] = $email;

        echo '<script>alert("Login Successful!");</script>';
        // Redirect to the next page
        echo '<script>window.location.href = "http://localhost/Inventory%20Management%20System/dashboard.php";</script>';

        exit;
    } else {
        echo '<script>alert("Login Failure!");</script>';
    }
} else {
    echo '<script>alert("Login Failure!");</script>';
}

$stmt->close();
$conn->close();
?>
