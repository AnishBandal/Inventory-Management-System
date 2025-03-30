<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

// Check if user is logged in
if (!isset($_SESSION["User_Id"])) {
    die(json_encode(["status" => "error", "message" => "Unauthorized access"]));
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$orderId = $data["orderId"] ?? null;
$orderType = $data["orderType"] ?? null; // 'purchase' or 'sales'
$user_Id = $_SESSION["User_Id"];

// Validate input
if (!$orderId || !is_numeric($orderId) || !in_array($orderType, ['purchase', 'sales'])) {
    die(json_encode(["status" => "error", "message" => "Invalid input data"]));
}

// Start transaction
$conn->begin_transaction();

try {
    if ($orderType === 'purchase') {
        // First check current status to prevent duplicate processing
        $checkStatusSql = "SELECT Status FROM purchase_orders WHERE PO_Id = ? AND User_Id = ?";
        $stmt = $conn->prepare($checkStatusSql);
        $stmt->bind_param("ii", $orderId, $user_Id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Purchase order not found");
        }
        
        $row = $result->fetch_assoc();
        if ($row['Status'] === 'Received') {
            throw new Exception("Purchase order already received");
        }
        
        // Get current stock levels before update for logging
        $getStockSql = "SELECT p.Product_Id, p.Name, p.Current_Stock, poi.Quantity
                       FROM purchase_order_items poi
                       JOIN products p ON poi.Product_Id = p.Product_Id
                       WHERE poi.PO_Id = ?";
        $stmt = $conn->prepare($getStockSql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stockResult = $stmt->get_result();
        
        $items = [];
        while ($item = $stockResult->fetch_assoc()) {
            $items[] = $item;
        }
        
        // Update purchase order status
        $updateSql = "UPDATE purchase_orders 
                     SET Status = 'Received', Actual_Delivery = CURDATE()
                     WHERE PO_Id = ? AND User_Id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $orderId, $user_Id);
        $stmt->execute();
        
        // Update product stock levels
        $updateStockSql = "UPDATE products p
                         JOIN purchase_order_items poi ON p.Product_Id = poi.Product_Id
                         SET p.Current_Stock = p.Current_Stock + poi.Quantity
                         WHERE poi.PO_Id = ?";
        $stmt = $conn->prepare($updateStockSql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        
        // Create inventory logs
        foreach ($items as $item) {
            $newStock = $item['Current_Stock'] + $item['Quantity'];
            $logSql = "INSERT INTO inventory_logs 
                      (Product_Id, User_Id, Reference_Type, Reference_Id, 
                       Quantity_Change, Old_Stock, New_Stock)
                      VALUES (?, ?, 'Purchase', ?, ?, ?, ?)";
            $stmt = $conn->prepare($logSql);
            $stmt->bind_param("iiiidd", 
                $item['Product_Id'], 
                $user_Id, 
                $orderId, 
                $item['Quantity'],
                $item['Current_Stock'],
                $newStock
            );
            $stmt->execute();
        }
        
    } else { // sales order
        // Similar structure for sales orders...
        // [Keep your existing sales order code, but ensure it follows the same pattern]
        
        // First check current status
        $checkStatusSql = "SELECT Status FROM sales_orders WHERE SO_Id = ? AND User_Id = ?";
        $stmt = $conn->prepare($checkStatusSql);
        $stmt->bind_param("ii", $orderId, $user_Id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Sales order not found");
        }
        
        $row = $result->fetch_assoc();
        if ($row['Status'] === 'Completed') {
            throw new Exception("Sales order already completed");
        }
        
        // Check stock availability
        $checkStockSql = "SELECT p.Product_Id, p.Name, p.Current_Stock, soi.Quantity
                         FROM sales_order_items soi
                         JOIN products p ON soi.Product_Id = p.Product_Id
                         WHERE soi.SO_Id = ?";
        $stmt = $conn->prepare($checkStockSql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stockResult = $stmt->get_result();
        
        $items = [];
        $outOfStockItems = [];
        while ($item = $stockResult->fetch_assoc()) {
            if ($item['Current_Stock'] < $item['Quantity']) {
                $outOfStockItems[] = $item['Name'] . " (Available: {$item['Current_Stock']}, Needed: {$item['Quantity']})";
            }
            $items[] = $item;
        }
        
        if (!empty($outOfStockItems)) {
            throw new Exception("Insufficient stock for: " . implode(", ", $outOfStockItems));
        }
        
        // Update sales order status
        $updateSql = "UPDATE sales_orders 
                     SET Status = 'Completed'
                     WHERE SO_Id = ? AND User_Id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $orderId, $user_Id);
        $stmt->execute();
        
        // Update product stock levels
        $updateStockSql = "UPDATE products p
                         JOIN sales_order_items soi ON p.Product_Id = soi.Product_Id
                         SET p.Current_Stock = p.Current_Stock - soi.Quantity
                         WHERE soi.SO_Id = ?";
        $stmt = $conn->prepare($updateStockSql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        
        // Create inventory logs
        foreach ($items as $item) {
            $newStock = $item['Current_Stock'] - $item['Quantity'];
            $logSql = "INSERT INTO inventory_logs 
                      (Product_Id, User_Id, Reference_Type, Reference_Id, 
                       Quantity_Change, Old_Stock, New_Stock)
                      VALUES (?, ?, 'Sale', ?, ?, ?, ?)";
            $stmt = $conn->prepare($logSql);
            $stmt->bind_param("iiiidd", 
                $item['Product_Id'], 
                $user_Id, 
                $orderId, 
                -$item['Quantity'],
                $item['Current_Stock'],
                $newStock
            );
            $stmt->execute();
        }
    }
    
    $conn->commit();
    echo json_encode(["status" => "success"]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>