<?php
session_start();
require 'db.php'; // Include your database connection

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get the event ID from the POST request
if (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Prepare a DELETE SQL statement
    $stmt = $conn->prepare("DELETE FROM weekly_events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    
    if ($stmt->execute()) {
        echo "success"; // Send a success response back
    } else {
        echo "error"; // Send an error response back
    }

    $stmt->close();
} else {
    echo "error"; // Send an error response if event_id is not provided
}

$conn->close();
?>
