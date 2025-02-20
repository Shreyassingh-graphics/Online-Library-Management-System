<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["user_type"] != "admin") {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "dbms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle cart approval
if (isset($_POST["approve"])) {
    $cart_id = $_POST["cart_id"];

    // Fetch the cart details
    $sql = "SELECT cart.cart_id, user.email, books.book_id, books.title, books.price, books.available, cart.user_id
            FROM cart
            JOIN user ON cart.user_id = user.user_id
            JOIN books ON cart.book_id = books.book_id
            WHERE cart.cart_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_data = $result->fetch_assoc();
        $stmt->close();

        if ($cart_data) {
            $book_id = $cart_data["book_id"];
            $available = $cart_data["available"];
            $user_id = $cart_data["user_id"];

            // Check if the book is available
            if ($available > 0) {
                // Define issue and return dates
                $issue_date = date("Y-m-d");
                $return_date = date(
                    "Y-m-d",
                    strtotime($issue_date . " + 30 days")
                );
                $fine = 0;
                $status = "Issued";

                // Insert the order into the orders table
                $insert_order_sql = "INSERT INTO orders (user_id, issue_date, return_date, fine, status, book_id)
                                     VALUES (?, ?, ?, ?, ?, ?)";
                if ($insert_stmt = $conn->prepare($insert_order_sql)) {
                    $insert_stmt->bind_param(
                        "issisi",
                        $user_id,
                        $issue_date,
                        $return_date,
                        $fine,
                        $status,
                        $book_id
                    );

                    if ($insert_stmt->execute()) {
                        // Reduce the book availability
                        $update_book_sql =
                            "UPDATE books SET available = available - 1 WHERE book_id = ?";
                        if ($update_stmt = $conn->prepare($update_book_sql)) {
                            $update_stmt->bind_param("i", $book_id);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }

                        // Delete the approved cart item
                        $delete_cart_sql = "DELETE FROM cart WHERE cart_id = ?";
                        if ($delete_stmt = $conn->prepare($delete_cart_sql)) {
                            $delete_stmt->bind_param("i", $cart_id);
                            $delete_stmt->execute();
                            $delete_stmt->close();

                            $_SESSION["message"] =
                                "Order successfully added and cart item removed.";
                        } else {
                            $_SESSION["error"] = "Failed to delete cart item.";
                        }
                    } else {
                        $_SESSION["error"] =
                            "Failed to add order to the database.";
                    }
                    $insert_stmt->close();
                } else {
                    $_SESSION["error"] =
                        "Failed to prepare order insertion query.";
                }
            } else {
                $_SESSION["error"] = "The book is not available.";
            }
        } else {
            $_SESSION["error"] = "Invalid cart ID.";
        }
    } else {
        $_SESSION["error"] = "Failed to prepare cart selection query.";
    }
}

// Fetch all cart requests
$sql = "SELECT cart.cart_id, user.email, books.title, books.price
        FROM cart
        JOIN user ON cart.user_id = user.user_id
        JOIN books ON cart.book_id = books.book_id";
$result = $conn->query($sql);
?>

<!-- HTML code remains unchanged -->

<?php $conn->close(); ?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Manage Cart Requests</title>
</head>
<body>
    <div class="sidebar">
    <h2 class="menu" style="color:#3BABF6">ADMIN MENU</h2>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="add_user.php">Add User</a>
        <a href="books_admin.php">Manage Books</a>
        <a href="orders.php">View Orders</a>
        <form method="POST" action="admin_dashboard.php">
            <button type="submit" name="signout" class="button signout">Sign Out</button>
        </form>
    </div>

    <h1>Manage Cart Requests</h1>
    <div class="container">
        <?php if (isset($_SESSION["message"])): ?>
            <p class="success"><?php
            echo $_SESSION["message"];
            unset($_SESSION["message"]);
            ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION["error"])): ?>
            <p class="error"><?php
            echo $_SESSION["error"];
            unset($_SESSION["error"]);
            ?></p>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <tr>
                    <th>Cart ID</th>
                    <th>User Email</th>
                    <th>Book Title</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["cart_id"]); ?></td>
                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                    <td><?php echo htmlspecialchars($row["price"]); ?></td>
                    <td>
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="cart_id" value="<?php echo $row[
                                "cart_id"
                            ]; ?>">
                            <button type="submit" name="approve" class="button">Approve</button>
                        </form>
                        <form method="POST" action="decline_cart.php" style="display:inline;">
                            <input type="hidden" name="cart_id" value="<?php echo $row[
                                "cart_id"
                            ]; ?>">
                            <button type="submit" name="decline" class="button">Decline</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No cart requests found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close();
?>
