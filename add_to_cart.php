<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: books_user.php?error=session_user_id_missing");
    exit();
}

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

// Validate if book_id is passed as a GET parameter
if (isset($_GET["up_id"])) {
    $book_id = intval($_GET["up_id"]); // Ensure book_id is an integer
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
                    // Redirect with success message
                    header("Location: books_user.php?success=1");
                } else {
                    // Redirect with failure message
                    header("Location: books_user.php?error=failed_to_add");
                }
                $add_to_cart_stmt->close();
            } else {
                // Redirect with already in cart message
                header("Location: books_user.php?error=already_in_cart");
            }
            $cart_check_stmt->close();
        } else {
            // Redirect with out of stock message
            header("Location: books_user.php?error=out_of_stock");
        }
    } else {
        // Redirect with invalid selection message
        header("Location: books_user.php?error=invalid_selection");
    }
    $book_check_stmt->close();
} else {
    // Redirect with invalid selection message if no book_id is passed
    header("Location: books_user.php?error=invalid_selection");
}

$conn->close();
?>
