<?php
session_start();
include 'db.php'; // Include your database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

// Get the event ID and new row count
$eventId = $_POST['event_id'];
$rowCount = $_POST['row_count'];

if (isset($eventId) && isset($rowCount)) {
    $sql = "UPDATE weekly_events SET row_count = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $rowCount, $eventId);
    if ($stmt->execute()) {
        echo "Row count updated successfully";
    } else {
        echo "Failed to update row count";
    }
    $stmt->close();
} else {
    echo "Invalid input";
}
$conn->close();
?>
