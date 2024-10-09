<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and fetch user input
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = trim($_POST['password']);

    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id']; // Store user ID in session
            $_SESSION['username'] = $user['username']; // Store username in session
            $_SESSION['role'] = $user['role']; // Store role in session (important for admin access)

            // Redirect to index.php
            header("Location: index.php");
            exit();
        } else {
            // Password is incorrect
            echo "Invalid password. <a href='login.php'>Try again</a>";
        }
    } else {
        // No user found with that username
        echo "No user found with that username. <a href='login.php'>Try again</a>";
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
    <title>Login - VIP Valet</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <link rel="icon" href="images/logo.png" type="image/png">

</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center">Login</h2>
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
