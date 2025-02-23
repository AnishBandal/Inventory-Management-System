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
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch purchase orders
$sql = "SELECT Purchase_Order_Id, Product_Id, Product, Supplier, Order_Date, Quantity, Cost, Status FROM product_details WHERE User_Id = '$user_Id' AND Purchase_Order_Id != 0";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Purchase Order ID</th>
                <th>Product ID</th>
                <th>Product</th>
                <th>Supplier</th>
                <th>Order Date</th>
                <th>Quantity</th>
                <th>Cost</th>
                <th>Status</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        $statusClass = strtolower($row["Status"]) === "completed" ? "completed" : "pending";
        echo "<tr>
                <td>{$row["Purchase_Order_Id"]}</td>
                <td>{$row["Product_Id"]}</td>
                <td>{$row["Product"]}</td>
                <td>{$row["Supplier"]}</td>
                <td>{$row["Order_Date"]}</td>
                <td>{$row["Quantity"]}</td>
                <td>{$row["Cost"]}</td>
                <td><button class='status-btn $statusClass' onclick='updateStatus({$row["Purchase_Order_Id"]})'>{$row["Status"]}</button></td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "<p class='no-records'>No records found.</p>";
}

$conn->close();

echo '<script>
  function updateStatus(purchaseOrderId) {
    const button = document.getElementById(`status_btn_${purchaseOrderId}`);
    if (button.textContent === "Pending") {
      button.textContent = "Completed";
      button.classList.remove("pending");
      button.classList.add("completed");
      button.disabled = true;
    }
  }
</script>';
?>