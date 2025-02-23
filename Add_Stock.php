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
    $supplier = $_POST['supplier'];   
    $order_date = $_POST['order_date'];
    $quantity = $_POST['quantity'];
    $cost = $_POST['cost'];   
    $status = $_POST['status'];

    // Retrieve the previous Purchase_Order_Id for the same User_Id
    $sql = "SELECT Purchase_Order_Id FROM product_details WHERE User_Id = '$user_Id' ORDER BY Purchase_Order_Id DESC LIMIT 1";
    $result = $conn->query($sql);
    $previousOrderId = 0;

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $previousOrderId = $row['Purchase_Order_Id'];
    }

    // Generate the new Purchase_Order_Id by incrementing the previous one
    $newOrderId = $previousOrderId + 1;

    $prod_sql = "SELECT Product_Id FROM product_details WHERE Product = '$product'";
    $prod_result = $conn->query($prod_sql);
        
    if ($prod_result->num_rows > 0) {
        $row = $prod_result->fetch_assoc();
        $product_id = $row['Product_Id'];
    }
    else{
    $Product_IDsql = "SELECT Product_Id FROM product_details WHERE User_Id = '$user_Id' ORDER BY Product_Id DESC LIMIT 1";
    $Product_Id_result = $conn->query($Product_IDsql);
    $previousProductId = 0;

    if ($Product_Id_result->num_rows > 0) {
        $row = $Product_Id_result->fetch_assoc();
        $previousProductId = $row['Product_Id'];
        $product_id = $previousProductId + 1;
    }
    else{
        $product_id =  1;
    }

    }
    



    // Fill the form values with the User_Id and generated Purchase_Order_Id
    $user_Id = htmlspecialchars($user_Id);
    $newOrderId = htmlspecialchars($newOrderId);




    // Insert the form values into the database
    $sql = "INSERT INTO product_details (User_Id, Purchase_Order_Id, Product_Id, Product, Supplier, Order_Date, Quantity, Cost, Status) 
            VALUES ('$user_Id', '$newOrderId', '$product_id', '$product', '$supplier', '$order_date', '$quantity', '$cost', '$status')";
    
    if ($conn->query($sql) === TRUE) {
        echo '<script>
        alert("Order Submitted Successfully");
        window.history.back();
        </script>';
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
