<?php
session_start();
if (!isset($_SESSION["email"]) || $_SESSION["user_type"] != "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "dbms");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST["approve"])) {
    $cart_id = $_POST["cart_id"];

    // Fetch the cart item details
    $sql = "SELECT cart.cart_id, user.email, books.book_id, books.title, books.price, books.available
            FROM cart
            JOIN user ON cart.user_id = user.user_id
            JOIN books ON cart.book_id = books.book_id
            WHERE cart.cart_id = ?";

    // Prepare the statement for fetching the cart item
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $cart_id); // Bind cart_id
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_data = $result->fetch_assoc();
        $stmt->close();

        // Check if cart data exists
        if ($cart_data) {
            $book_id = $cart_data["book_id"];
            $available = $cart_data["available"];
            $user_id = $cart_data["user_id"];
            $title = $cart_data["title"];
            $price = $cart_data["price"];

            // Check if the book is available
            if ($available > 0) {
                // Define issue and return dates
                $issue_date = date("Y-m-d");
                $return_date = date(
                    "Y-m-d",
                    strtotime($issue_date . " + 30 days")
                ); // Assuming a 30-day issue period
                $fine = 0; // No fine initially
                $status = "Issued"; // Default status for a newly issued book

                // Insert the order into the orders table
                $insert_order_sql = "INSERT INTO orders (user_id, issue_date, return_date, fine, status, book_id)
                                     VALUES (?, ?, ?, ?, ?, ?)";

                if ($insert_stmt = $conn->prepare($insert_order_sql)) {
                    $insert_stmt->bind_param(
                        "iissi",
                        $user_id,
                        $issue_date,
                        $return_date,
                        $fine,
                        $status,
                        $book_id
                    );
                    if ($insert_stmt->execute()) {
                        // Decrease the book availability by 1 after successfully inserting the order
                        $update_book_sql =
                            "UPDATE books SET available = available - 1 WHERE book_id = ?";

                        if ($update_stmt = $conn->prepare($update_book_sql)) {
                            $update_stmt->bind_param("i", $book_id);
                            $update_stmt->execute();
                            $update_stmt->close();

                            // Delete the approved cart item
                            $delete_cart_sql =
                                "DELETE FROM cart WHERE cart_id = ?";

                            if (
                                $delete_stmt = $conn->prepare($delete_cart_sql)
                            ) {
                                $delete_stmt->bind_param("i", $cart_id);
                                $delete_stmt->execute();
                                $delete_stmt->close();

                                $_SESSION["message"] =
                                    "Order successfully added and cart item removed.";
                            } else {
                                $_SESSION["error"] =
                                    "Failed to delete cart item after approving.";
                            }
                        } else {
                            $_SESSION["error"] =
                                "Failed to update book availability.";
                        }
                    } else {
                        $_SESSION["error"] =
                            "Failed to add order to the database. Please try again.";
                    }
                    $insert_stmt->close();
                } else {
                    $_SESSION["error"] =
                        "Failed to prepare order insertion query. Please try again.";
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

// Redirect back to the cart page
header("Location: admin_cart.php");
exit(); // Ensure script execution stops after redirect

$conn->close();
?>
