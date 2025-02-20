<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["user_type"] != "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dbms");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user deletion
if (isset($_GET["user_id"])) {
    $user_id = intval($_GET["user_id"]);
    $delete_query = "DELETE FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $message = "User deleted successfully!";
    } else {
        $message = "Error deleting user: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all users
$sql = "SELECT * FROM user";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Manage Users</title>
</head>
<body>
    <div class="sidebar">
        <h2 class="menu" style="color:#3BABF6">ADMIN MENU</h2>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="add_user.php">Add User</a>
        <a href="books_admin.php">Manage Books</a>
        <a href="admin_cart.php">View Cart</a>
        <a href="orders.php">View Orders</a>
        <form method="POST" action="admin_dashboard.php">
            <button type="submit" name="signout" class="button signout">Sign Out</button>
        </form>
    </div>

    <h1>Manage Users</h1>
    <div class="container">
        <table class="table">
            <tr>
                <th>User ID</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row["user_id"]); ?></td>
                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                <td>

                    <a href="?user_id=<?php echo $row[
                        "user_id"
                    ]; ?>" class="button" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

<?php $conn->close();
?>
