<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and fetch user input
    $first_name = $conn->real_escape_string(trim($_POST['first_name']));
    $last_name = $conn->real_escape_string(trim($_POST['last_name']));
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : NULL;

    // Prepare SQL statement to insert user
    $sql = "INSERT INTO users (first_name, last_name, username, password, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $first_name, $last_name, $username, $password, $email);

    if ($stmt->execute()) {
        // Registration successful, redirect to login page
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - VIP Valet</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <link rel="icon" href="images/logo.png" type="image/png">

</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center">Register</h2>
                <form action="register.php" method="post">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (optional)</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a>.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
