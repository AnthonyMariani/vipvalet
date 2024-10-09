<?php
session_start();
include 'db.php'; // Include your database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];

    // Query to get available employees on the selected date
    $sql = "
        SELECT users.id, users.first_name, users.last_name, availability.time_type, availability.time, availability.all_day
        FROM availability
        JOIN users ON availability.user_id = users.id
        WHERE availability.date = ?
        AND availability.status = 'available'
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $availableEmployees = [];
    while ($row = $result->fetch_assoc()) {
        $availabilityDetail = '';

        // Check the availability condition
        if ($row['all_day'] == 1) {
            $availabilityDetail = "Available all day";
        } elseif ($row['time_type'] === 'after') {
            $availabilityDetail = "Available after " . date("g:i A", strtotime($row['time']));
        } elseif ($row['time_type'] === 'before') {
            $availabilityDetail = "Available before " . date("g:i A", strtotime($row['time']));
        }

        // Add employee information to the array, including availability details
        $availableEmployees[] = [
            'id' => $row['id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'availability' => $availabilityDetail
        ];
    }

    $stmt->close();

    // Return available employees in JSON format
    echo json_encode($availableEmployees);
}
?>
