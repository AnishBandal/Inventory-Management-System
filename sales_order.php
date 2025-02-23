<?php
session_start();

$email = isset($_SESSION['Email']) ? $_SESSION['Email'] : '';

?>

<!DOCTYPE html>
<html>
<head>
  <title>Sales Order</title>
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

    <h2>Sales History</h2>
    
    <button type="button" class="button" onclick="location.href = 'Remove_Stock.html'">
      <span class="button__text">Add Sales</span>
      <span class="button__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" viewBox="0 0 24 24" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" stroke="currentColor" height="24" fill="none" class="svg"><line y2="19" y1="5" x2="12" x1="12"></line><line y2="12" y1="12" x2="19" x1="5"></line></svg></span>
    </button>


    <div class="products_table">
    <iframe src="Display_Sales_Product.php" frameborder="0" width="100%" height="400"></iframe>
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
