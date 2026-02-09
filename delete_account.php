<?php
session_start();
include('connect.php'); 

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // SQL query to delete the user account
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Account successfully deleted
        session_destroy();
        header("Location: login.html");
        exit();
    } else {
        echo "Error deleting account.";
        exit(); // Prevent further execution
    }
} else {
    header("Location: login.html"); // Always redirect if not logged in
    exit();
}
?>
