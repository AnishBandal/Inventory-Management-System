<?php
session_start();

$user_Id = $_SESSION['User_Id'] ?? null;

if ($user_Id === null) {
    die("User ID not found in the session.");
}

$conn = new mysqli("localhost", "root", "", "inventory_management_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product = $_POST['product'];
    $customer = $_POST['customer'];   
    $sales_date = $_POST['sales_date'];
    $quantity = $_POST['quantity'];
    $price = $_POST['cost'];
    $status = $_POST['status'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Get or create customer
        $customer_parts = explode(" ", $customer, 2);
        $firstName = $customer_parts[0];
        $lastName = $customer_parts[1] ?? '';
        
        $customer_sql = "SELECT Customer_Id FROM customers 
                         WHERE FirstName = ? AND LastName = ?";
        $stmt = $conn->prepare($customer_sql);
        $stmt->bind_param("ss", $firstName, $lastName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $customer_id = $row['Customer_Id'];
        } else {
            $insert_customer = "INSERT INTO customers 
                              (FirstName, LastName) 
                              VALUES (?, ?)";
            $stmt = $conn->prepare($insert_customer);
            $stmt->bind_param("ss", $firstName, $lastName);
            $stmt->execute();
            $customer_id = $conn->insert_id;
        }

        // 2. Get product ID and check stock
        $product_sql = "SELECT Product_Id, Current_Stock 
                       FROM products WHERE Name = ?";
        $stmt = $conn->prepare($product_sql);
        $stmt->bind_param("s", $product);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception("Product not found");
        }
        
        $row = $result->fetch_assoc();
        $product_id = $row['Product_Id'];
        $current_stock = $row['Current_Stock'];
        
        if ($current_stock < $quantity) {
            throw new Exception("Insufficient stock available");
        }

        // 3. Create sales order
        $so_sql = "INSERT INTO sales_orders 
                  (User_Id, Customer_Id, Order_Date, Status) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($so_sql);
        $stmt->bind_param("iiss", $user_Id, $customer_id, $sales_date, $status);
        $stmt->execute();
        $so_id = $conn->insert_id;

        // 4. Add sales order items
        $soi_sql = "INSERT INTO sales_order_items 
                   (SO_Id, Product_Id, Quantity, Unit_Price) 
                   VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($soi_sql);
        $stmt->bind_param("iiid", $so_id, $product_id, $quantity, $price);
        $stmt->execute();

        $conn->commit();
        
        echo '<script>
              alert("Sales Order Submitted Successfully");
              window.location.href = "Display_Sales_Product.php";
              </script>';
    } catch (Exception $e) {
        $conn->rollback();
        echo '<script>
              alert("Error: '.$e->getMessage().'");
              window.history.back();
              </script>';
    }
}

$conn->close();
?>