<?php
session_start();

echo '<style>
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes slideIn {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    animation: fadeIn 0.5s ease-in-out;
  }

  table th,
  table td {
    padding: 12px;
    border: 1px solid #DDDDDD;
    text-align: left;
    animation: slideIn 0.3s ease-in-out;
  }

  table th {
    background-color: #2C3E50; /* Dark blue */
    color: #FFFFFF;
    font-weight: 600;
  }

  table tr:nth-child(even) {
    background-color: #F9F9F9;
  }

  table tr:hover {
    background-color: #F1F1F1;
  }

  .status-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.3s ease;
  }

  .status-btn.pending {
    background-color: #FFA500;
    color: #FFFFFF;
  }

  .status-btn.completed {
    background-color: #4CAF50;
    color: #FFFFFF;
    cursor: not-allowed;
  }

  .no-records {
    text-align: center;
    font-style: italic;
    color: #999;
    margin-bottom: 20px;
    animation: fadeIn 0.5s ease-in-out;
  }
</style>';

// Retrieve the User_Id from the session

$user_Id = $_SESSION['User_Id'] ?? null;

if ($user_Id === null) {
    die("User ID not found in the session.");
}

// Database connection
$conn = new mysqli("localhost", "root", "", "inventory_management_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Updated query for new structure
$sql = "SELECT 
            po.PO_Id AS Purchase_Order_Id,
            p.Product_Id,
            p.Name AS Product,
            s.Name AS Supplier,
            poi.Quantity,
            poi.Unit_Cost AS Cost,
            (poi.Quantity * poi.Unit_Cost) AS Amount,
            po.Order_Date,
            po.Status,
            po.Expected_Delivery,
            po.Actual_Delivery
        FROM purchase_orders po
        JOIN purchase_order_items poi ON po.PO_Id = poi.PO_Id
        JOIN products p ON poi.Product_Id = p.Product_Id
        JOIN suppliers s ON po.Supplier_Id = s.Supplier_Id
        WHERE po.User_Id = '$user_Id'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Purchase Order ID</th> 
                <th>Supplier</th>
                <th>Product</th>
                <th>Quantity</th>       
                <th>Unit Cost</th>
                <th>Total Cost</th>
                <th>Order Date</th>
                <th>Expected Delivery</th>
                <th>Status</th>
            </tr>";

            while ($row = $result->fetch_assoc()) {
              $statusClass = strtolower($row["Status"]) === "received" ? "completed" : "pending";
              
              // Handle delivery date display
              $deliveryDate = '';
              if ($row["Status"] === "Received" && isset($row["Actual_Delivery"])) {
                  $deliveryDate = "Delivered: ".$row["Actual_Delivery"];
              } else if (isset($row["Expected_Delivery"])) {
                  $deliveryDate = "Expected: ".$row["Expected_Delivery"];
              } else {
                  $deliveryDate = "Not specified";
              }
              
              echo "<tr>
                      <td>{$row["Purchase_Order_Id"]}</td>
                      <td>{$row["Supplier"]}</td>
                      <td>{$row["Product"]}</td>
                      <td>{$row["Quantity"]}</td>
                      <td>{$row["Cost"]}</td>
                      <td>{$row["Amount"]}</td>
                      <td>{$row["Order_Date"]}</td>
                      <td>{$deliveryDate}</td>
                      <td><button class='status-btn $statusClass' data-purchase-id='{$row["Purchase_Order_Id"]}' onclick='updateStatus(this)'>{$row["Status"]}</button></td>
                    </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='no-records'>No purchase orders found.</p>";
}

$conn->close();

echo '<script>
function updateStatus(button) {
  const purchaseOrderId = button.dataset.purchaseId;

  fetch("update_status.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ 
      orderId: purchaseOrderId,
      orderType: "purchase"
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === "success") {
      button.textContent = "Completed";
      button.classList.remove("pending");
      button.classList.add("completed");
      button.disabled = true;
    } else {
      alert("Error updating status: " + data.message);
    }
  })
  .catch(error => {
    console.error("Error:", error);
  });
}

</script>';
?>