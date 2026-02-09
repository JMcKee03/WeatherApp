<?php
session_start(); // Start session to access user data

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please log in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Database connection
    $conn = new mysqli("localhost", "root", "", "weather_app");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];

    // Update only fields that are filled
    if (!empty($email) && !empty($password)) {
        $sql = "UPDATE users SET email=?, password=? WHERE ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $email, $password, $user_id);
    } elseif (!empty($email)) {
        $sql = "UPDATE users SET email=? WHERE ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $user_id);
    } elseif (!empty($password)) {
        $sql = "UPDATE users SET password=? WHERE ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $password, $user_id);
    } else {
        die("No changes made.");
    }

    if ($stmt->execute()) {
        echo "Account updated successfully!";
    } else {
        echo "Error updating account: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
