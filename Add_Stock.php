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
    $supplier = $_POST['supplier'];   
    $order_date = $_POST['order_date'];
    $expected_delivery = $_POST['expected_delivery'];
    $quantity = $_POST['quantity'];
    $cost = $_POST['cost'];
    $status = $_POST['status'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Get or create supplier
        $supplier_sql = "SELECT Supplier_Id FROM suppliers WHERE Name = ?";
        $stmt = $conn->prepare($supplier_sql);
        $stmt->bind_param("s", $supplier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $supplier_id = $row['Supplier_Id'];
        } else {
            $insert_supplier = "INSERT INTO suppliers (Name) VALUES (?)";
            $stmt = $conn->prepare($insert_supplier);
            $stmt->bind_param("s", $supplier);
            $stmt->execute();
            $supplier_id = $conn->insert_id;
        }

        // 2. Get or create product
        $product_sql = "SELECT Product_Id FROM products WHERE Name = ?";
        $stmt = $conn->prepare($product_sql);
        $stmt->bind_param("s", $product);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $product_id = $row['Product_Id'];
        } else {
            // Create new product with default values
            $insert_product = "INSERT INTO products 
                             (Name, Current_Stock, Unit_Price, Reorder_Level) 
                             VALUES (?, 0, ?, 5)"; // Default reorder level of 5
            $stmt = $conn->prepare($insert_product);
            $default_price = $cost * 1.2; // Adding 20% margin as default selling price
            $stmt->bind_param("sd", $product, $default_price);
            $stmt->execute();
            $product_id = $conn->insert_id;
        }

        // 3. Create purchase order
        $po_sql = "INSERT INTO purchase_orders 
                  (User_Id, Supplier_Id, Order_Date, Expected_Delivery, Status) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($po_sql);
        $stmt->bind_param("iisss", $user_Id, $supplier_id, $order_date, $expected_delivery, $status);
        $stmt->execute();
        $po_id = $conn->insert_id;

        // 4. Add purchase order items
        $poi_sql = "INSERT INTO purchase_order_items 
                   (PO_Id, Product_Id, Quantity, Unit_Cost) 
                   VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($poi_sql);
        $stmt->bind_param("iiid", $po_id, $product_id, $quantity, $cost);
        $stmt->execute();

        $conn->commit();
        
        echo '<script>
              alert("Purchase Order Submitted Successfully");
              window.location.href = "purchase_order.php";
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