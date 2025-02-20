<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["user_type"] != "admin") {
    header("Location: login.php");
    exit(); // Ensure the script stops after redirection
}

// Database connection
$conn = new mysqli("localhost", "root", "", "dbms");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch orders from the database including the book_id column
$sql = "SELECT * FROM orders";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Manage Orders</title>
</head>
<body>
    <div class="sidebar">
    <h2 class="menu" style="color:#3BABF6">ADMIN MENU</h2>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="add_user.php">Add User</a>
        <a href="add_book.php">Add Book</a>
        <a href="admin_cart.php">View Cart</a>
        <a href="orders.php" class="active">View Orders</a>
        <form method="POST" action="admin_dashboard.php">
            <button type="submit" name="signout" class="button signout">Sign Out</button>
        </form>
    </div>

    <h1>Manage Orders</h1>
    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Book ID</th> <!-- New Book ID Column -->
                    <th>Issue Date</th>
                    <th>Issue Days</th>
                    <th>Return Date</th>
                    <th>Fine</th>
                    <th>Status</th> <!-- Status Column -->
                    <th>Action</th> <!-- Action Column to Update Status -->
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["order_id"]); ?></td>
                    <td><?php echo htmlspecialchars($row["user_id"]); ?></td>
                    <td><?php echo htmlspecialchars(
                        $row["book_id"]
                    ); ?></td> <!-- Display Book ID -->
                    <td><?php echo htmlspecialchars($row["issue_date"]); ?></td>
                    <td><?php echo htmlspecialchars($row["issue_days"]); ?></td>
                    <td><?php echo htmlspecialchars(
                        $row["return_date"]
                    ); ?></td>
                    <td><?php echo htmlspecialchars($row["fine"]); ?></td>
                    <td><?php echo htmlspecialchars($row["status"]); ?></td>
                    <td>
                        <form method="POST" action="update_order_status.php">
                            <input type="hidden" name="order_id" value="<?php echo $row[
                                "order_id"
                            ]; ?>">
                            <input type="hidden" name="book_id" value="<?php echo $row[
                                "book_id"
                            ]; ?>"> <!-- Pass Book ID -->
                            <select name="status" required>
                                <option value="Due" <?php if (
                                    $row["status"] == "Due"
                                ) {
                                    echo "selected";
                                } ?>>Due</option>
                                <option value="Returned" <?php if (
                                    $row["status"] == "Returned"
                                ) {
                                    echo "selected";
                                } ?>>Returned</option>
                                <option value="Late" <?php if (
                                    $row["status"] == "Late"
                                ) {
                                    echo "selected";
                                } ?>>Late</option>
                            </select>
                            <button type="submit" class="button">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); // Close the database connection
?>
