<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["user_type"] != "admin") {
    header("Location: login.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "dbms");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert user into the database
    $sql = "INSERT INTO user (email, pass) VALUES ('$email', '$password')";
    if ($conn->query($sql) === true) {
        echo "New user added successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Add User</title>
</head>
<body>
    <div class="sidebar">
    <h2 class="menu" style="color:#3BABF6">ADMIN MENU</h2>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="user.php">Manage Users</a>
        <a href="books_admin.php">Manage Books</a>
        <a href="admin_cart.php">View Cart</a>
        <a href="orders.php">View Orders</a>
        <form method="POST" action="admin_dashboard.php">
            <button type="submit" name="signout" class="button signout">Sign Out</button>
        </form>
    </div>

    <h1>Add New User</h1>
    <div class="container">
        <form method="POST" action="add_user.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>
            <button type="submit" class="button">Add User</button>
        </form>
    </div>
</body>
</html>
