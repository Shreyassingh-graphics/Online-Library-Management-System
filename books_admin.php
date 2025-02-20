<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] != "admin") {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "dbms");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete Request
if (isset($_GET["de_id"])) {
    $book_id = intval($_GET["de_id"]);
    $delete_query = "DELETE FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $book_id);

    if ($stmt->execute()) {
        $message = "Book deleted successfully!";
    } else {
        $message = "Error deleting book: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all books
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

    <h1>Manage Books (Admin)</h1>

    <!-- Display Messages -->
    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <div class="container">
        <table class="table">
            <tr>
                <th>Book ID</th>
                <th>Title</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Available</th>
                <th>Author</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row["book_id"]; ?></td>
                <td><?php echo htmlspecialchars($row["title"]); ?></td>
                <td><?php echo htmlspecialchars($row["price"]); ?></td>
                <td><?php echo htmlspecialchars($row["quantity"]); ?></td>
                <td><?php echo htmlspecialchars($row["available"]); ?></td>
                <td><?php echo htmlspecialchars($row["author"]); ?></td>
                <td>
                    <!-- Update Button -->
                    <a href="adminBookUpdate.php?up_id=<?php echo $row[
                        "book_id"
                    ]; ?>" class="button">Update</a>
                    <!-- Delete Button -->
                    <a href="?de_id=<?php echo $row[
                        "book_id"
                    ]; ?>" class="button" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
