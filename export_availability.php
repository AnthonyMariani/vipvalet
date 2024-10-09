<?php
session_start();
include 'db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get the selected week start date
$start_date = $_POST['week_select'];
$end_date = date('Y-m-d', strtotime($start_date . ' +6 days'));

// Convert dates to a user-friendly format
$start_date_friendly = date('F j, Y', strtotime($start_date));
$end_date_friendly = date('F j, Y', strtotime($end_date));

// Fetch availability data from the database
$sql = "SELECT u.first_name, u.last_name, a.date, a.status, a.all_day, a.time_type, a.time
        FROM availability a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.date BETWEEN ? AND ?
        ORDER BY u.last_name, u.first_name, a.date";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$availability_data = [];
while ($row = $result->fetch_assoc()) {
    $availability_data[$row['first_name'] . ' ' . $row['last_name']][$row['date']] = $row;
}

$stmt->close();
$conn->close();

// Set the headers for the CSV file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=availability_' . $start_date . '_to_' . $end_date . '.csv');

// Open the output stream
$output = fopen('php://output', 'w');

// Write the header row with the week of the date range
fputcsv($output, ["Week of $start_date_friendly to $end_date_friendly"]);

// Write the days of the week as the header row
$days_of_week = ["Employee", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday", "Monday", "Tuesday"];
fputcsv($output, $days_of_week);

// Initialize the date array to map the days of the week
$dates = [];
for ($i = 0; $i < 7; $i++) {
    $dates[date('Y-m-d', strtotime($start_date . " +$i days"))] = $days_of_week[$i + 1];
}

// Write each employee's availability
foreach ($availability_data as $employee_name => $availability) {
    $row = [$employee_name];
    foreach ($dates as $date => $day) {
        if (isset($availability[$date])) {
            $status = $availability[$date]['status'];
            if ($status == 'available') {
                if ($availability[$date]['all_day'] == 1) {
                    $row[] = 'Available All Day (Green)';
                } else {
                    $time_type = ucfirst($availability[$date]['time_type']);
                    $time = date('g:i A', strtotime($availability[$date]['time']));
                    $row[] = "Available $time_type $time (Yellow)";
                }
            } else {
                $row[] = 'Unavailable (Red)';
            }
        } else {
            $row[] = ''; // No availability data for this day
        }
    }
    fputcsv($output, $row);
}

// Close the output stream
fclose($output);
?>
