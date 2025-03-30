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
            so.SO_Id AS Sales_Order_Id,
            p.Product_Id,
            p.Name AS Product,
            c.FirstName, c.LastName,
            CONCAT(c.FirstName, ' ', c.LastName) AS Customer,
            soi.Quantity,
            soi.Unit_Price AS Cost,
            (soi.Quantity * soi.Unit_Price) AS Amount,
            so.Order_Date AS Sales_Date,
            so.Status
        FROM sales_orders so
        JOIN sales_order_items soi ON so.SO_Id = soi.SO_Id
        JOIN products p ON soi.Product_Id = p.Product_Id
        JOIN customers c ON so.Customer_Id = c.Customer_Id
        WHERE so.User_Id = '$user_Id'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Amount</th> 
                <th>Order Date</th>
                <th>Status</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        $statusClass = strtolower($row["Status"]) === "completed" ? "completed" : "pending";
        echo "<tr>
                <td>{$row["Sales_Order_Id"]}</td>
                <td>{$row["Customer"]}</td>
                <td>{$row["Product"]}</td>              
                <td>{$row["Quantity"]}</td>
                <td>{$row["Cost"]}</td>
                <td>{$row["Amount"]}</td>
                <td>{$row["Sales_Date"]}</td>
                <td><button class='status-btn $statusClass' data-sales-id='{$row["Sales_Order_Id"]}' onclick='updateStatus(this)'>{$row["Status"]}</button></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='no-records'>No sales orders found.</p>";
}

$conn->close();

echo '<script>
function updateStatus(button) {
  const salesOrderId = button.dataset.salesId;

  fetch("update_status.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ 
      orderId: salesOrderId,
      orderType: "sales"
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