<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'dbms');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if order_id and status are set in POST
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    // Update the order status in the database
    $update_sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        // Redirect back to orders page with success message
        header("Location: orders.php?status_updated=1");
        exit();
    } else {
        echo "Error updating status.";
    }
    
    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
