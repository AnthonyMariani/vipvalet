<?php
session_start();
include 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignments = json_decode(file_get_contents("php://input"), true);

    foreach ($assignments as $assignment) {
        if (!isset($assignment['event_id'], $assignment['employee_id'], $assignment['start_time'])) {
            continue; // Skip if any key is missing
        }

        $eventId = $assignment['event_id'];
        $employeeId = $assignment['employee_id'];
        $startTime = $assignment['start_time'];

        // Check if assignment already exists for the same event and employee
        $sqlCheck = "SELECT id FROM event_assignments WHERE event_id = ? AND employee_id = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("ii", $eventId, $employeeId);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            // If assignment exists, update it
            $row = $resultCheck->fetch_assoc();
            $assignmentId = $row['id'];

            $sqlUpdate = "UPDATE event_assignments SET start_time = ?, updated_at = NOW() WHERE id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("si", $startTime, $assignmentId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        } else {
            // If assignment doesn't exist, insert a new one
            $sqlInsert = "INSERT INTO event_assignments (event_id, employee_id, start_time, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("iis", $eventId, $employeeId, $startTime);
            $stmtInsert->execute();
            $stmtInsert->close();
        }

        $stmtCheck->close();
    }

    $conn->close();
    echo json_encode(["status" => "success"]);
}
?>
