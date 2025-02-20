<?php
session_start();
include "db_config.php";

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $user_type = $_POST["user_type"];

    if ($user_type == "admin") {
        $sql = "SELECT * FROM admin WHERE email='$email' AND pass='$password'";
    } else {
        $sql = "SELECT * FROM user WHERE email='$email' AND pass='$password'";
    }

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); // Fetch the row
        $_SESSION["email"] = $email;
        $_SESSION["user_type"] = $user_type;

        if ($user_type == "admin") {
            header("Location: admin_dashboard.php");
        } else {
            $_SESSION["user_id"] = $row["user_id"]; // Assign user_id to session
            header("Location: user_dashboard.php");
        }
    } else {
        echo "<p style='color: red; text-align: center;'>Invalid login credentials!</p>";
    }
    exit();
}

// Handle registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $email = $_POST["reg_email"];
    $password = $_POST["reg_password"];

    $sql = "INSERT INTO user (email, pass) VALUES ('$email', '$password')";
    if ($conn->query($sql) === true) {
        echo "<p style='color: green; text-align: center;'>Registration successful!</p>";
    } else {
        echo "<p style='color: red; text-align: center;'>Error: " .
            $sql .
            "<br>" .
            $conn->error .
            "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login & Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        h3{
            text-align: center;
            color: #333;
            background: #f3f4f6;
            padding:15px;
            border-radius: 15px;
            height: 25px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin: 10px 0 5px;
            color: #555;
            font-size: 15px;
        }
        input, select, button {
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            font-size: 15px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .form-section {
            margin-bottom: 20px;
        }

        .error, .success {
            text-align: center;
            margin-top: 10px;
            font-size: 24px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Login</h3>
        <form method="POST" action="login.php">
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <label>Login as:</label>
            <select name="user_type">
                <option value="user">User Login</option>
                <option value="admin">Admin Login</option>
            </select>
            <button type="submit" name="login">Login</button>
        </form>

        <h3>Register</h3>
        <form method="POST" action="login.php">
            <label>Email:</label>
            <input type="email" name="reg_email" required>
            <label>Password:</label>
            <input type="password" name="reg_password" required>
            <button type="submit" name="register">Register</button>
        </form>
    </div>
</body>
</html>
