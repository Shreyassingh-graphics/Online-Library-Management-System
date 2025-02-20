<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $available = $_POST['available'];
    $author = $_POST['author'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'dbms');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert book into the database
    $sql = "INSERT INTO books (title, price, quantity, available, author) VALUES ('$title', '$price', '$quantity', '$available', '$author')";
    if ($conn->query($sql) === TRUE) {
        echo "New book added successfully.";
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
    <title>Add Book</title>
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
    
    <h1>Add New Book</h1>
    <div class="container">
        <form method="POST" action="add_book.php">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required><br><br>
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" required><br><br>
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required><br><br>
            <label for="available">Available:</label>
            <input type="number" id="available" name="available" required><br><br>
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" required><br><br>
            <button type="submit" class="button">Add Book</button>
        </form>
    </div>
</body>
</html>
