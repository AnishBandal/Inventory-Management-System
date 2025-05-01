<?php
session_start();

// Retrieve the User_Id from the session
$user_Id = $_SESSION['User_Id'] ?? null;

// Check if User_Id is available
if ($user_Id === null) {
    die("User ID not found in the session.");
}


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


// fetch & sanitize
$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// search both name & description if you like, here just name
$sql = "
  SELECT Product_Id, Product_Name, Description
  FROM Products
  WHERE Product_Name LIKE '%{$query}%'
  LIMIT 10
";
$res = $conn->query($sql);

$products = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($products);
$conn->close();
?>