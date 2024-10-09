<?php
session_start(); // Start the session
include 'session_check.php'; // Ensure the user is logged in
include 'db.php'; // Include the database connection

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // SQL to delete availability for the given user
    $sql = "DELETE FROM availability WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "Availability cleared successfully";
    } else {
        http_response_code(500);
        echo "Error clearing availability: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "Invalid request";
}
?>
