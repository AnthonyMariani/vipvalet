<?php
session_start();
include 'db.php'; // Include your database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Check if the request method is POST and if the required data is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $event_id = $_POST['id'];
    $event_date = $_POST['event_date'] ?? null;
    $client = $_POST['client'] ?? null;
    $location = $_POST['location'] ?? null;
    $hours_of_event = $_POST['hours_of_event'] ?? null;
    $service_hours = $_POST['service_hours'] ?? null;
    $permit_address = $_POST['permit_address'] ?? null;
    $permit_number = $_POST['permit_number'] ?? null;
    $staff_needed = $_POST['staff_needed'] ?? null;
    $complimentary = $_POST['complimentary'] ?? null;
    $contact = $_POST['contact'] ?? null;
    $details = $_POST['details'] ?? null;

    // Prepare an UPDATE SQL statement
    $stmt = $conn->prepare("UPDATE weekly_events 
                            SET event_date = ?, client = ?, location = ?, hours_of_event = ?, service_hours = ?, 
                                permit_address = ?, permit_number = ?, staff_needed = ?, complimentary = ?, contact = ?, details = ?, updated_at = NOW() 
                            WHERE id = ?");
    
    // Bind the parameters to the SQL statement
    $stmt->bind_param("sssssssssssi", $event_date, $client, $location, $hours_of_event, $service_hours, 
                                    $permit_address, $permit_number, $staff_needed, $complimentary, $contact, $details, $event_id);
    
    // Execute the query and check if it was successful
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error; // Output error message for debugging
    }

    $stmt->close(); // Close the statement
} else {
    echo "error: Missing required parameters";
}

$conn->close(); // Close the database connection
?>
