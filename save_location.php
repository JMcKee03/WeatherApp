<?php
// save_location.php
session_start();
require 'config.php';

if(!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Not logged in.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = trim($_POST['location']);
    if($location) {
        // Check if location already exists for the user
        $stmt = $pdo->prepare("SELECT * FROM saved_locations WHERE user_id = ? AND location = ?");
        $stmt->execute([$_SESSION['user_id'], $location]);
        if(!$stmt->fetch()){
            $stmt = $pdo->prepare("INSERT INTO saved_locations (user_id, location) VALUES (?, ?)");
            if($stmt->execute([$_SESSION['user_id'], $location])){
                echo "Location saved.";
            } else {
                http_response_code(500);
                echo "Failed to save location.";
            }
        } else {
            echo "Location already saved.";
        }
    } else {
        http_response_code(400);
        echo "Invalid location.";
    }
}
?>
