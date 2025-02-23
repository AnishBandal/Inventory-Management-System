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

// Prepare the SQL statement to get in-stock quantity
$sql = "SELECT Product_Id, Product, 
               SUM(CASE WHEN Sales_Order_Id = 0 THEN Quantity ELSE -Quantity END) AS InStock_Quantity
        FROM product_details
        WHERE User_Id = '$user_Id'
        GROUP BY Product_Id";

// Execute the SQL statement
$result = $conn->query($sql);

// Check if any records were found
if ($result->num_rows > 0) {
    // Display the records in a table
    echo "<table>
            <tr>
                <th>Product ID</th>
                <th>Product</th>
                <th>In-Stock Quantity</th>
            </tr>";

            while ($row = $result->fetch_assoc()) {
              echo "<tr>
                      <td>".$row["Product_Id"]."</td>
                      <td>".$row["Product"]."</td>
                      <td>".$row["InStock_Quantity"]."</td>
                    </tr>";
          }
          

    echo "</table>";
} else {
    echo "No records found.";
}

// Close the database connection
$conn->close();
?>
