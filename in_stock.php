<?php
session_start();

$email = isset($_SESSION['Email']) ? $_SESSION['Email'] : '';

?>

<!DOCTYPE html>
<html>
<head>
  <title>In Stock</title>
  <link rel="stylesheet" href="purchase_order.css">
</head>
<body>
  <div class="sidebar" id="sidebar">
    <!-- Sidebar content here -->
    <ul>
      <li class="navMenu"><a href="http://localhost/Inventory%20Management%20System/purchase_order.php">Purchase Order</a></li>
      <li class="navMenu"><a href="http://localhost/Inventory%20Management%20System/sales_order.php">Sales Order</a></li>
      <li class="navMenu"><a href="http://localhost/Inventory%20Management%20System/in_stock.php">In Stock</a></li>
    </ul>
  </div>

  <nav>

  <div class="hamburger" onclick="toggleSidebar()">
    <div class="hamburger-line"></div>
    <div class="hamburger-line"></div>
    <div class="hamburger-line"></div>
  </div> 
  
  <p id="userName">Hello</p>

  </nav>

  <div class="content" id="content">

    <h2>In Stock Product</h2>
    

    <div class="products_table">
    <iframe src="Display_Stock_Product.php" frameborder="0" width="100%" height="400"></iframe>
    </div>

  </div>
  

  
  <script>

    function toggleSidebar() {
      var sidebar = document.getElementById("sidebar");
      var content = document.getElementById("content");
      sidebar.classList.toggle("open");
      content.classList.toggle("open");
    }




    window.onload = function() {
      // Retrieve the email from the session storage
      var email = "<?php echo $email; ?>";

      // Set the email to the HTML element
      var displayElement = document.getElementById("userName");
      displayElement.textContent = email;

};

  </script>
</body>
</html>
