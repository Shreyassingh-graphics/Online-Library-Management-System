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

if (isset($_POST["decline"])) {
    $cart_id = $_POST["cart_id"];

    // Delete the cart item
    $sql = "DELETE FROM cart WHERE cart_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    header("Location: admin_cart.php");
}

// Redirect back to the cart page
$conn->close();
?>
