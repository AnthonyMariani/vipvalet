<?php
session_start(); // Start the session

require 'db.php'; // Include the database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_date = $_POST['event_date'];
    $client = $_POST['client'];
    $location = $_POST['location'];
    $hours_of_event = $_POST['hours_of_event'];
    $service_hours = $_POST['service_hours'];
    $permit_address = $_POST['permit_address'];
    $permit_number = $_POST['permit_number'];
    $staff_needed = $_POST['staff_needed'];
    $complimentary = $_POST['complimentary'];
    $contact = $_POST['contact'];
    $details = $_POST['details'];

    // Prepare the SQL statement to insert a new event
    $stmt = $conn->prepare("INSERT INTO weekly_events (
        event_date, client, location, hours_of_event, service_hours, permit_address, permit_number, staff_needed, complimentary, contact, details, status, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())");
    
    // Bind parameters to the SQL statement
    $stmt->bind_param("sssssssssss", $event_date, $client, $location, $hours_of_event, $service_hours, $permit_address, $permit_number, $staff_needed, $complimentary, $contact, $details);
    
    // Execute the query
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close(); // Close the prepared statement
    $conn->close(); // Close the database connection
}
?>
