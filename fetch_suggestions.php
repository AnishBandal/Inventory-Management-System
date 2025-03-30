<?php
session_start();

$user_Id = $_SESSION['User_Id'] ?? null;

if ($user_Id === null) {
    die("User ID not found in the session.");
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_management_system";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = $_GET['query'];
$type = $_GET['type'];

$query = $conn->real_escape_string($query);
$type = ($type==="product") ? "Product" : "Supplier";

$sql = "SELECT DISTINCT $type from product_details WHERE $type LIKE '%$query%' AND User_Id = '$user_Id'";
$result = $conn->query($sql);

$suggestions = [];
while($row = $result->fetch_assoc){
    $suggestions[] = $row[$type];
}

echo json_encode($suggestions);
$conn->close();

?>
