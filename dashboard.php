<?php
session_start();
$email = isset($_SESSION['Email']) ? $_SESSION['Email'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Inventory Management</title>
  <link rel="stylesheet" href="dashboard.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <!-- Navbar -->
  <nav>
    <div class="navbar-left">
      <h1>Inventory Management</h1>
    </div>
    <div class="navbar-right">
      <div class="user-dropdown">
        <button class="user-btn">
          <i class="fas fa-user-circle"></i>
          <span><?php echo $email; ?></span>
          <i class="fas fa-chevron-down"></i>
        </button>
        <div class="dropdown-content">
          <a href="#"><i class="fas fa-user"></i> Profile</a>
          <a href="#"><i class="fas fa-cog"></i> Settings</a>
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="content">
    <h2>Dashboard</h2>
    <div class="card-container">
      <div class="card" onclick="location.href='purchase_order.php'">
        <i class="fas fa-cart-plus"></i>
        <h3>Purchase Order</h3>
        <p>Manage your purchase orders.</p>
      </div>
      <div class="card" onclick="location.href='sales_order.php'">
        <i class="fas fa-chart-line"></i>
        <h3>Sales Order</h3>
        <p>Track your sales orders.</p>
      </div>
      <div class="card" onclick="location.href='in_stock.php'">
        <i class="fas fa-boxes"></i>
        <h3>In Stock</h3>
        <p>View your current inventory.</p>
      </div>
      <!-- Add more cards for future pages -->
    </div>
  </div>

  <script>
    // Toggle dropdown menu
    const userBtn = document.querySelector(".user-btn");
    const dropdownContent = document.querySelector(".dropdown-content");

    userBtn.addEventListener("click", function (event) {
      event.stopPropagation(); // Prevent the click from bubbling up
      dropdownContent.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    window.addEventListener("click", function () {
      if (dropdownContent.classList.contains("show")) {
        dropdownContent.classList.remove("show");
      }
    });
  </script>
</body>
</html>