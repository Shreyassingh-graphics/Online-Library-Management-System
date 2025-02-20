<?php
session_start();

// Ensure the user is logged in and of type 'user'
if (!isset($_SESSION["email"]) || $_SESSION["user_type"] != "user") {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "dbms");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user_id from session
$user_id = intval($_SESSION["user_id"]);

// Fetch the books in the user's cart
$cart_sql = "
    SELECT cart.cart_id, books.book_id, books.title, books.author, books.price
    FROM cart
    JOIN books ON cart.book_id = books.book_id
    WHERE cart.user_id = ?";
$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param("i", $_SESSION["user_id"]); // binding the session user_id securely
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_stmt->close();

// Calculate total price
$total_price = 0;
$cart_rows = []; // Store rows for display
while ($row = $cart_result->fetch_assoc()) {
    $total_price += $row["price"];
    $cart_rows[] = $row;
}

// Handle book removal from cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cart_id"])) {
    $cart_id = intval($_POST["cart_id"]);
    $book_id = intval($_POST["book_id"]);

    // Delete the book from the cart
    $delete_sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $cart_id, $user_id);

    // Execute delete statement and check for success
    if ($delete_stmt->execute()) {
        // Refresh the page to show updated cart
        header("Location: user_cart.php");
        exit();
    } else {
        echo "Failed to remove book from cart.";
    }

    $delete_stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Your Cart</title>
</head>
<body>
    <!-- Sidebar for navigation -->
    <div class="sidebar">
        <h2 class="menu" style="color:#3BABF6">User Menu</h2>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="books_user.php">Books</a>
        <a href="user_orders.php">My Orders</a>
        <form method="POST" action="user_dashboard.php">
            <button type="submit" name="signout" class="button signout" style="margin: 10px 0; width: 100%;">Sign Out</button>
        </form>
    </div>

    <!-- Main content -->
    <div class="container">
        <h1>Your Cart</h1>
        <?php if (count($cart_rows) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Book ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_rows as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(
                                $row["book_id"]
                            ); ?></td>
                            <td><?php echo htmlspecialchars(
                                $row["title"]
                            ); ?></td>
                            <td><?php echo htmlspecialchars(
                                $row["author"]
                            ); ?></td>
                            <td><?php echo htmlspecialchars(
                                $row["price"]
                            ); ?></td>
                            <td>
                                <form method="POST" action="user_cart.php">
                                    <input type="hidden" name="cart_id" value="<?php echo $row[
                                        "cart_id"
                                    ]; ?>">
                                    <input type="hidden" name="book_id" value="<?php echo $row[
                                        "book_id"
                                    ]; ?>">
                                    <button type="submit" class="button">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); // Close the database connection ?>
