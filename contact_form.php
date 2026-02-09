<?php

require_once 'connect.php'; // assumes $pdo is defined here

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Clean user inputs
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = strip_tags(trim($_POST["message"]));

    // Validate inputs
    if (empty($name) || empty($email) || empty($message)) {
        die("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // --- INSERT INTO DB ---
    try {
        $stmt = $pdo->prepare("INSERT INTO contact (name, email, message) VALUES (:name, :email, :message)");
        $stmt->execute([
            ":name" => $name,
            ":email" => $email,
            ":message" => $message
        ]);
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

    // --- EMAIL LOGIC ---
    $to = "jmckee03@icloud.com";
    $subject = "New Contact Form Submission from $name";
    $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
    $headers = "From: noreply@example.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send the email
    if (mail($to, $subject, $body, $headers)) {
        echo "Message sent and saved successfully!";
    } else {
        echo "Message saved, but email failed to send.";
    }

} else {
    echo "Invalid request.";
}
