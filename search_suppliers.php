<?php
session_start();

// Retrieve the User_Id from the session
$user_Id = $_SESSION['User_Id'] ?? null;

if ($user_Id === null) {
    die("User ID not found in the session.");
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch and sanitize query
$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// Search suppliers
$sql = "
  SELECT Supplier_Name FROM suppliers
  WHERE Supplier_Name LIKE '%{$query}%'
  LIMIT 10
";

$res = $conn->query($sql);

$suppliers = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($suppliers);
$conn->close();
?>