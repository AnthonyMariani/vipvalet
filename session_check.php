<?php
session_start(); // Start the session

// Log session details
fwrite($log_handle, "Session Check - " . date('Y-m-d H:i:s') . "\n");
fwrite($log_handle, "Session Data: " . print_r($_SESSION, true) . "\n");

// Check if the user is logged in by checking if 'user_id' is set in the session
if (!isset($_SESSION['user_id'])) {
    fwrite($log_handle, "Redirecting to index.php because user_id is not set.\n");
    fclose($log_handle); // Close the log file
    header("Location: index.php");
    exit();
}

fwrite($log_handle, "User is logged in. Proceeding with the request.\n");
fclose($log_handle); // Close the log file
?>
