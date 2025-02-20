<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["user_type"] != "admin") {
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
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="sidebar">
        <h2 class="menu" style="color:#3BABF6">ADMIN MENU</h2>
        <a href="user.php">Manage Users</a>
        <a href="books_admin.php">Manage Books</a>
        <a href="admin_cart.php">View Cart Requests</a>
        <a href="orders.php">View Orders</a>
        <form method="POST" action="admin_dashboard.php">
            <button type="submit" name="signout" class="button signout">Sign Out</button>
        </form>
    </div>

    <h1>Admin Dashboard</h1>
    <div class="container">
        <h2>Options</h2>
        <div class="dashboard-container">
            <div class="dashboard-box">
               <button><a href="add_user.php">Add User</a>
            </div>
            <div class="dashboard-box">
                <button><a href="add_book.php">Add Book</a>
            </div>
        </div>
    </div>
</body>
</html>
