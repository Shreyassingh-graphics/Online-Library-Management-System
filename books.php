<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'dbms');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all books from the database
$sql = "SELECT * FROM books";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Manage Books (Admin)</title>
</head>
<body>
    <div class="sidebar">
    <h2 class="menu" style="color:#3BABF6">ADMIN MENU</h2>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="add_user.php">Add User</a>
        <a href="add_book.php">Add Book</a>
        <a href="admin_cart.php">View Cart Requests</a>
        <a href="orders.php">View Orders</a>
        <form method="POST" action="admin_dashboard.php">
            <button type="submit" name="signout" class="button signout">Sign Out</button>
        </form>
    </div>

    <h1>Available Books (Admin)</h1>
    <div class="container">
        <table class="table">
            <tr>
                <th>Book ID</th>
                <th>Title</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Available</th>
                <th>Author</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['book_id']; ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['price']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                <td><?php echo htmlspecialchars($row['available']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
