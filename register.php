<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_management_system";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to securely hash the password using bcrypt
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Retrieve form data and sanitize
$firstname = htmlspecialchars(trim($_POST["firstName"]));
$lastname = htmlspecialchars(trim($_POST["lastName"]));
$email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
$phoneno = htmlspecialchars(trim($_POST["phoneNumber"]));
$password = $_POST["password"];
$created_At = date('Y-m-d H:i:s');

// Validate Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("Invalid Email Format!"); window.history.back();</script>';
    exit;
}

// Check if email already exists
$query = "SELECT COUNT(*) FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    echo '<script>alert("Email already exists in the database!"); window.history.back();</script>';
    exit;
}

// If form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hashedPassword = hashPassword($password);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (FirstName, LastName, Email, Phone_No, Password, Created_At) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $firstname, $lastname, $email, $phoneno, $hashedPassword, $created_At);

    if ($stmt->execute()) {
        $Id = $conn->insert_id; 
        $_SESSION['User_Id'] = $Id;
        $_SESSION['Email'] = $email;

        echo '<script>
            alert("Data saved successfully");
            window.location.href = "http://localhost/Inventory%20Management%20System/dashboard.php";
        </script>';
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
