<?php
session_start();

// Retrieve the User_Id from the session
$user_Id = $_SESSION['User_Id'] ?? null;

if ($user_Id === null) {
    die(json_encode(['error' => 'User ID not found in session']));
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Fetch and sanitize query
$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// Search suppliers with contact info
$sql = "SELECT Supplier_Id, Supplier_Name, Contact_Info 
        FROM Suppliers 
        WHERE Supplier_Name LIKE '%{$query}%'
        LIMIT 10";

$res = $conn->query($sql);

$suppliers = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $suppliers[] = [
            'Supplier_Id' => $row['Supplier_Id'],
            'Supplier_Name' => $row['Supplier_Name'],
            'Contact_Info' => $row['Contact_Info']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($suppliers);
$conn->close();
?>