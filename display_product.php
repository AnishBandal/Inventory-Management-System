
<?php
session_start();

echo '<style>
@keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }
  
  @keyframes slideIn {
    from {
      transform: translateY(50px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    animation: fadeIn 0.5s ease-in-out;
  }
  
  table th,
  table td {
    padding: 10px;
    border: 1px solid #ccc;
    animation: slideIn 0.3s ease-in-out;
  }
  
  table th {
    background-color: #333;
    color: #fff;
    font-weight: bold;
  }
  
  table tr:nth-child(even) {
    background-color: #f9f9f9;
  }
  
  table tr:hover {
    background-color: #e5e5e5;
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

// Prepare the SQL statement
$sql = "SELECT Purchase_Order_Id, Product_Id, Product, Supplier, Order_Date, Quantity, Cost, Status FROM product_details WHERE User_Id = '$user_Id' AND Purchase_Order_Id != 0";

// Execute the SQL statement
$result = $conn->query($sql);

// Check if any records were found
if ($result->num_rows > 0) {
    // Display the records in a table
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
              echo "<tr>
                      <td>".$row["Purchase_Order_Id"]."</td>
                      <td>".$row["Product_Id"]."</td>
                      <td>".$row["Product"]."</td>
                      <td>".$row["Supplier"]."</td>
                      <td>".$row["Order_Date"]."</td>
                      <td>".$row["Quantity"]."</td>
                      <td>".$row["Cost"]."</td>
                      <td><button id='status_btn_".$row["Purchase_Order_Id"]."' onclick='updateStatus(".$row["Purchase_Order_Id"].")'>".$row["Status"]."</button></td>
                    </tr>";
          }
          

    echo "</table>";
} else {
    echo "No records found.";
}

// Close the database connection
$conn->close();

echo '<script> function updateStatus() {
  var button = document.getElementById("status_btn_").value;

  if (button == "Pending") {
    button.textContent = "Completed";
    button.disabled = true;
  }
} </script>';
?>
