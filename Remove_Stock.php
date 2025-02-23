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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form values;
    $product = $_POST['product'];
    $customer = $_POST['customer'];   
    $sales_date = $_POST['sales_date'];
    $quantity = $_POST['quantity'];
    $cost = $_POST['cost'];   
    $status = $_POST['status'];

    // Retrieve the previous Sales_Order_Id for the same User_Id
    $sql = "SELECT Sales_Order_Id FROM product_details WHERE User_Id = '$user_Id' ORDER BY Sales_Order_Id DESC LIMIT 1";
    $result = $conn->query($sql);
    $previousOrderId = 0;

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $previousOrderId = $row['Sales_Order_Id'];
    }

    // Generate the new Sales_Order_Id by incrementing the previous one
    $newOrderId = $previousOrderId + 1;

    // Retrieve the previous Sales_Order_Id for the same User_Id
    $prod_sql = "SELECT Product_Id FROM product_details WHERE Product = '$product' AND User_Id = '$user_Id'";
    $prod_result = $conn->query($prod_sql);
        
    if ($prod_result->num_rows > 0) {
        $row = $prod_result->fetch_assoc();
        $product_id = $row['Product_Id'];
    }
    else{
        echo '<script>alert("Product not Found");</script>';
        echo '<script>window.location.href = document.referrer;</script>';
        exit;
    }
    



    // Fill the form values with the User_Id and generated Purchase_Order_Id
    $user_Id = htmlspecialchars($user_Id);
    $newOrderId = htmlspecialchars($newOrderId);

    // Insert the form values into the database
    $sql = "INSERT INTO product_details (User_Id, Sales_Order_Id, Product_Id, Product, Customer, Sales_Date, Quantity, Cost, Status) 
            VALUES ('$user_Id', '$newOrderId', '$product_id', '$product', '$customer', '$sales_date', '$quantity', '$cost', '$status')";
    
    if ($conn->query($sql) === TRUE) {
        echo '<script>
        alert("Sales Submitted Successfully");
        window.history.back();
        </script>';
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
