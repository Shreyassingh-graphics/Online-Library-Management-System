<?php
session_start();

// Ensure the user is logged in and of type 'user'
if (!isset($_SESSION["email"]) || $_SESSION["user_type"] != "user") {
    header("Location: login.php");
    exit(); // Ensure the script stops after redirection
}

// Database connection
$conn = new mysqli("localhost", "root", "", "dbms");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from session (assuming email is unique and maps to user_id)
$email = $_SESSION["email"];
$user_query = "SELECT user_id FROM user WHERE email = ?";
if ($stmt = $conn->prepare($user_query)) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    if (!$user_id) {
        die("User not found.");
    }
} else {
    die("Error preparing user query: " . $conn->error);
}

// Fetch orders for the logged-in user
$sql = "SELECT * FROM orders WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Error preparing orders query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Manage Orders</title>
</head>
<body>
    <!-- Sidebar for navigation -->
    <div class="sidebar">
        <h2 class="menu" style="color:#3BABF6">User Menu</h2>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="books_user.php">Books</a>
        <a href="user_cart.php">Cart</a>
        <form method="POST" action="user_dashboard.php">
            <button type="submit" name="signout" class="button signout" style="margin: 10px 0; width: 100%;">Sign Out</button>
        </form>
    </div>

    <!-- Main content -->
    <div class="container">
        <h1>My Orders</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Book ID</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Fine</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(
                            $row["order_id"]
                        ); ?></td>
                        <td><?php echo htmlspecialchars(
                            $row["user_id"]
                        ); ?></td>
                        <td><?php echo htmlspecialchars(
                            $row["book_id"]
                        ); ?></td>
                        <td><?php echo htmlspecialchars(
                            $row["issue_date"]
                        ); ?></td>
                        <td><?php echo htmlspecialchars(
                            $row["return_date"]
                        ); ?></td>
                        <td><?php echo htmlspecialchars($row["fine"]); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close(); // Close the statement
$conn->close(); // Close the database connection


?>
