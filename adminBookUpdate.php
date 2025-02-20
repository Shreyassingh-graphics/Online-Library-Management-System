<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION["user_type"]) || $_SESSION["user_type"] != "admin") {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "dbms");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the book data if `up_id` is set
if (isset($_GET["up_id"])) {
    $book_id = intval($_GET["up_id"]);
    $query = "SELECT * FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
    } else {
        die("No book found with the provided ID.");
    }
    $stmt->close();
}

// Update the book details when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $price = $_POST["price"];
    $quantity = $_POST["quantity"];
    $available = $_POST["available"];
    $author = $_POST["author"];

    $update_query =
        "UPDATE books SET title = ?, price = ?, quantity = ?, available = ?, author = ? WHERE book_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param(
        "sdiisi",
        $title,
        $price,
        $quantity,
        $available,
        $author,
        $book_id
    );
    if ($update_stmt->execute()) {
        header("Location: books_admin.php");
        exit();
    } else {
        echo "Error updating book: " . $conn->error;
    }
    $update_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Update Book</title>
</head>
<body>
    <h1>Update Book Details</h1>
    <form method="POST" action="">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars(
            $book["title"]
        ); ?>" required><br><br>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars(
            $book["price"]
        ); ?>" step="0.01" required><br><br>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars(
            $book["quantity"]
        ); ?>" required><br><br>

        <label for="available">Available:</label>
        <input type="number" id="available" name="available" value="<?php echo htmlspecialchars(
            $book["available"]
        ); ?>" required><br><br>

        <label for="author">Author:</label>
        <input type="text" id="author" name="author" value="<?php echo htmlspecialchars(
            $book["author"]
        ); ?>" required><br><br>

        <button type="submit" >Update Book</button>
    </form>
</body>
</html>
