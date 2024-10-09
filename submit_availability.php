<?php
session_start();
require 'db.php'; // Assuming you have a file called db.php to handle the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session
    $availability = $_POST['availability']; // Expecting availability data as an associative array

    // Prepare the SQL statement with placeholders
    $query = "INSERT INTO availability (user_id, date, status, all_day, time_type, time)
              VALUES (?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE status = VALUES(status), all_day = VALUES(all_day), time_type = VALUES(time_type), time = VALUES(time)";
    
    $stmt = $conn->prepare($query);

    // Iterate over the availability data and bind the values
    foreach ($availability as $date => $entry) {
        $status = $entry['status'];

        // Determine the values of all_day, time_type, and time based on status
        if ($status === 'unavailable') {
            $all_day = NULL;
            $time_type = NULL;
            $time = NULL;
        } elseif ($entry['all_day']) {
            $all_day = 1;
            $time_type = NULL;
            $time = NULL;
        } else {
            $all_day = 0;
            $time_type = $entry['time_type'] ? $entry['time_type'] : NULL;
            $time = $entry['time'] ? $entry['time'] : NULL;
        }

        // Bind parameters and execute the statement
        $stmt->bind_param("ississ", $user_id, $date, $status, $all_day, $time_type, $time);
        $stmt->execute();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Return a success message
    echo json_encode(["message" => "Availability successfully submitted!"]);
}
?>
