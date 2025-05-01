<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['User_Id'])) {
    die(json_encode(['error' => 'Unauthorized access']));
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

// Get form data
$product_id = $conn->real_escape_string($_POST['product_id']);
$supplier = $conn->real_escape_string($_POST['supplier']);
$supplier_contact = $conn->real_escape_string($_POST['supplier_contact']);
$order_date = $conn->real_escape_string($_POST['order_date']);
$quantity = (int)$_POST['quantity'];
$cost = (float)$_POST['cost'];
$status = $conn->real_escape_string($_POST['status']);
$description = $conn->real_escape_string($_POST['description']);
$user_id = $_SESSION['User_Id'];

// Validate required fields
if (empty($product_id) || empty($supplier) || empty($order_date) || empty($quantity) || empty($cost)) {
    die(json_encode(['error' => 'All required fields must be filled']));
}

// Transaction start
$conn->begin_transaction();

try {
    // 1. Check if product exists
    $product_check = $conn->query("SELECT * FROM Products WHERE Product_Id = '$product_id' AND User_Id = '$user_id'");
    if ($product_check->num_rows == 0) {
        throw new Exception("Product not found");
    }
    $product = $product_check->fetch_assoc();

    // 2. Check if supplier exists (by name)
    $supplier_check = $conn->query("SELECT * FROM Suppliers WHERE Supplier_Name = '$supplier'");
    if ($supplier_check->num_rows == 0) {
        // Create new supplier with contact info
        $conn->query("INSERT INTO Suppliers (Supplier_Name, Contact_Info) VALUES ('$supplier', '$supplier_contact')");
        $supplier_id = $conn->insert_id;
    } else {
        $supplier_data = $supplier_check->fetch_assoc();
        $supplier_id = $supplier_data['Supplier_Id'];
        // Update contact info if changed
        if ($supplier_contact != $supplier_data['Contact_Info']) {
            $conn->query("UPDATE Suppliers SET Contact_Info = '$supplier_contact' WHERE Supplier_Id = '$supplier_id'");
        }
    }

    // 3. Get next Purchase_Order_Id for this user
    $order_id_result = $conn->query("SELECT COALESCE(MAX(Purchase_Order_Id), 0) + 1 AS next_id 
                                    FROM Purchase_Orders 
                                    WHERE User_Id = '$user_id'");
    $next_order_id = $order_id_result->fetch_assoc()['next_id'];

    // 4. Create the purchase order
    $conn->query("INSERT INTO Purchase_Orders (User_Id, Purchase_Order_Id, Supplier_Id, Order_Date, Status)
                  VALUES ('$user_id', '$next_order_id', '$supplier_id', '$order_date', '$status')");

    // 5. Add order items
    $conn->query("INSERT INTO Order_Items (User_Id, Purchase_Order_Id, Product_Id, Quantity, Cost, Amount)
                  VALUES ('$user_id', '$next_order_id', '$product_id', '$quantity', '$cost', '$cost')");

    // 6. Update product description if changed (optional)
    if (!empty($description) && $description != $product['Description']) {
        $conn->query("UPDATE Products SET Description = '$description' WHERE Product_Id = '$product_id'");
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>