<?php
session_start();

// Check if the user is logged in as a user
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] != "user") {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "dbms");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for messages
$success_message = "";
$error_message = "";

// Handle Add to Cart functionality
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["book_id"])) {
    $book_id = intval($_POST["book_id"]); // Ensure book_id is an integer
    $user_id = $_SESSION["user_id"]; // Assuming user_id is stored in session

    // Check if the book exists and is available
    $book_check_query = "SELECT available FROM books WHERE book_id = ?";
    $book_check_stmt = $conn->prepare($book_check_query);
    $book_check_stmt->bind_param("i", $book_id);
    $book_check_stmt->execute();
    $book_check_result = $book_check_stmt->get_result();

    if ($book_check_result->num_rows > 0) {
        $book = $book_check_result->fetch_assoc();
        if ($book["available"] > 0) {
            // Check if the book is already in the user's cart
            $cart_check_query =
                "SELECT * FROM cart WHERE user_id = ? AND book_id = ?";
            $cart_check_stmt = $conn->prepare($cart_check_query);
            $cart_check_stmt->bind_param("ii", $user_id, $book_id);
            $cart_check_stmt->execute();
            $cart_check_result = $cart_check_stmt->get_result();

            if ($cart_check_result->num_rows == 0) {
                // Add the book to the cart
                $add_to_cart_query =
                    "INSERT INTO cart (user_id, book_id) VALUES (?, ?)";
                $add_to_cart_stmt = $conn->prepare($add_to_cart_query);
                $add_to_cart_stmt->bind_param("ii", $user_id, $book_id);

                if ($add_to_cart_stmt->execute()) {
                    $success_message = "Book successfully added to cart.";
                } else {
                    $error_message =
                        "Failed to add book to cart. Please try again.";
                }
                $add_to_cart_stmt->close();
            } else {
                $error_message = "Book is already in your cart.";
            }
            $cart_check_stmt->close();
        } else {
            $error_message = "Book is out of stock.";
        }
    } else {
        $error_message = "Invalid book selection.";
    }
    $book_check_stmt->close();
}

// Fetch books from the database
$books_query = "SELECT * FROM books";
$books_result = $conn->query($books_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="sidebar">
    <h2 class="menu" style="color:#3BABF6">User Menu</h2>
    <a href="user_dashboard.php">Dashboard</a>
            <a href="user_cart.php">Cart</a>
            <a href="user_orders.php">My Orders</a>
            <form method="POST" action="user_dashboard.php">
                <button type="submit" name="signout" class="button signout" style="margin: 10px 0; width: 100%;">Sign Out</button>
            </form>
        </div>

    <div class="content">
        <h1>Available Books</h1>

        <!-- Display Success or Error Messages -->
        <?php if ($success_message): ?>
            <p style="color: green;"> <?php echo htmlspecialchars(
                $success_message
            ); ?> </p>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <p style="color: red;"> <?php echo htmlspecialchars(
                $error_message
            ); ?> </p>
        <?php endif; ?>

        <!-- Books Table -->
        <div class="container">
            <table class="table">
                <tr>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Available</th>
                    <th>Action</th>
                </tr>

                <?php while ($book = $books_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(
                            $book["book_id"]
                        ); ?></td>
                        <td><?php echo htmlspecialchars($book["title"]); ?></td>
                        <td><?php echo htmlspecialchars(
                            $book["author"]
                        ); ?></td>
                        <td><?php echo htmlspecialchars(
                            $book["available"]
                        ); ?></td>
                        <td>
                            <?php if ($book["available"] > 0): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="book_id" value="<?php echo $book[
                                        "book_id"
                                    ]; ?>">
                                    <button type="submit" class="button">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <button disabled class="button">Out of Stock</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
        </table>
    </div>

    <?php $conn->close(); ?>
</body>
</html>
