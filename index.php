<?php
session_start(); // Start the session at the top of the file

// Check if the user is logged in
$logged_in = isset($_SESSION['user_id']);
$is_admin = $logged_in && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIP Valet</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <link rel="icon" href="images/logo.png" type="image/png">

</head>
<body>
    <!-- Include the Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="text-center">
            <h1 class="display-4"></h1>
            <p class="lead">
                <?php if (!$logged_in): ?>
                    Please login or register to continue.
                <?php endif; ?>
            </p>
        </div>

        <!-- Conditional Sections for Logged-in Users -->
        <?php if ($logged_in): ?>
            <div class="row justify-content-center mt-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Edit Availability</h5>
                            <p class="card-text">Manage your weekly availability for scheduling.</p>
                            <a href="availability.php" class="btn btn-primary">Edit Availability</a>
                        </div>
                    </div>
                </div>

                <?php if ($is_admin): ?>
                    <div class="col-md-4 mt-4 mt-md-0">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Admin Privileges</h5>
                                <p class="card-text">Access admin features and manage the team.</p>
                                <a href="admin.php" class="btn btn-danger">Admin Dashboard</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-5">
    <img src="images/logo.png" alt="VIP Valet Logo" style="max-width: 150px;">
    </div>


    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
