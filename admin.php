<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VIP Valet</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <link rel="icon" href="images/logo.png" type="image/png">
    <style>
        body {
            margin-top: 80px; /* Adjust margin to avoid overlap with fixed navbar */
        }
    </style>
</head>
<body>
    <!-- Include the Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Back to Home Button -->
    <div class="container mt-5">
        <a href="index.php" class="btn btn-secondary mb-3">&larr; Back to Home</a>
    </div>

    <div class="container mt-5">
        <h1 class="text-center">Admin Dashboard</h1>

        <!-- Admin Options Grid -->
        <div class="row mt-4">
            <!-- Generate CSV Option -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Generate CSV</h5>
                        <p class="card-text">Export availability data to a CSV file.</p>
                        <form action="export_availability.php" method="post">
                            <div class="mb-3">
                                <label for="week_select" class="form-label">Select Week</label>
                                <select id="week_select" name="week_select" class="form-control" required>
                                    <?php foreach ($weeks as $week): ?>
                                        <option value="<?php echo $week['start']; ?>"><?php echo $week['display']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Generate CSV</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Manage Events Option -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Events</h5>
                        <p class="card-text">Create, view, and update upcoming events.</p>
                        <a href="manage_events.php" class="btn btn-secondary">Go to Manage Events</a>
                    </div>
                </div>
            </div>

            <!-- Manage Schedule Option -->
            <div class="col-md-4 mt-4 mt-md-0">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Schedule</h5>
                        <p class="card-text">View and manage the weekly schedule for events.</p>
                        <a href="manage_schedule.php" class="btn btn-secondary">Go to Manage Schedule</a>
                    </div>
                </div>
            </div>

            <!-- Add more cards as needed -->
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
