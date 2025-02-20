<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["user_type"] != "user") {
    header("Location: login.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["signout"])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>User Dashboard</title>
</head>
<body>
    <div class="sidebar">
    <h2 class="menu" style="color:#3BABF6">User Menu</h2>
        <a href="books_user.php">Books</a>
        <a href="user_cart.php">Cart</a>
        <a href="user_orders.php">My Orders</a>
        <form method="POST" action="user_dashboard.php">
            <button type="submit" name="signout" class="button signout" style="margin: 10px 0; width: 100%;">Sign Out</button>
        </form>
    </div>

    <div class="container" style="margin-left: 220px;">
        <h1>User Dashboard</h1>
        <div class="dashboard-container">
            <h2>Welcome, <?php echo $_SESSION["email"]; ?>!</h2>
        </div>
    </div>
</body>
</html>
