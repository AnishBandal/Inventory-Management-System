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
    die("Connection failed: " . $conn->connect_error);  //Use this if you want to see PHP errors
}

// Function to securely hash the password using bcrypt
function hashPassword($password) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    return $hashedPassword;
}

// Retrieve the previous ID
$sql = "SELECT MAX(User_Id) AS max_id FROM registration_details";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $previousId = $row["max_id"];
    $newId = $previousId + 1;
}

$email = $_POST['email']; // Assuming you retrieve the email from a form

// Query to check if email exists
$query = "SELECT COUNT(*) FROM registration_details WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    // Email exists in the database, show alert message
    echo '<script>alert("Email already exists in the database!");</script>';
    echo '<script>window.history.back();</script>'; // Go back to the form
    exit;
}



// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Id = $newId;
    $firstname = $_POST["firstName"];
    $lastname = $_POST["lastName"];
    $email = $_POST["email"];
    $phoneno = $_POST["phoneNumber"];
    $password = $_POST["password"];


    // Hash the password
    $hashedPassword = hashPassword($password);

    // Prepare and bind the SQL statement
    $stmt = $conn->prepare("INSERT INTO registration_details(User_Id, FirstName, LastName, Email, Phone_No, Password) VALUES(?,?, ?, ?, ?, ?)");
    $stmt->bind_param('isssss',$Id, $firstname, $lastname, $email, $phoneno, $hashedPassword);

    // Execute the statement
    if ($stmt->execute()) {
        
        $_SESSION['User_Id'] = $Id;
        $_SESSION['Email'] = $email;

       echo ' <script>
        alert("Data saved successfully");
        window.location.href = "http://localhost/Inventory%20Management%20System/dashboard.php";
        </script>
        ';
        
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>