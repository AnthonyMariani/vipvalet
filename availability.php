<?php
include 'session_check.php'; // Ensure the user is logged in
include 'db.php'; // Include the database connection

// Fetch current availability for the logged-in user
$user_id = $_SESSION['user_id'];
$sql = "SELECT date, status, all_day, time_type, time FROM availability WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$availabilityData = [];

while ($row = $result->fetch_assoc()) {
    $availabilityData[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Availability</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="calendar.css">
    <link rel="icon" href="images/logo.png" type="image/png">

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
    <script>
    var availabilityData = <?php echo json_encode($availabilityData); ?>;
    </script>

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="index.php" style="text-decoration: none;">
            VIP Valet
        </a>

        <!-- Toggle button for mobile view -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Back Button -->
<div class="container mt-3">
    <a href="index.php" class="btn btn-secondary">&larr; Back to Home</a>
</div>


<div class="container">
    <h1>Submit Your Availability</h1>
    <div id='calendar'></div>
    <button id="submitAvailability">Submit Availability</button>
    <button id="clearAvailability" class="btn btn-danger mt-3">Clear Availability</button>

</div>

<!-- The Modal -->
<div id="availabilityModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Set Availability for <span id="selectedDate"></span></h2>
        <label for="status">Status:</label>
        <select id="status">
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
        </select>

        <!-- All Day Option -->
        <div class="all-day-option" id="allDayOption">
            <input type="checkbox" id="allDay">
            <label for="allDay">All Day</label>
        </div>

        <div id="timeInputs">
            <label for="availabilityTime">Available:</label>
            <select id="availabilityType">
                <option value="after">After</option>
                <option value="until">Until</option>
            </select>
            <input type="time" id="availabilityTime">
        </div>

        <button id="saveAvailability">Save</button>
        
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var weeklyAvailability = {}; // Object to store availability for the week

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridWeek',
            firstDay: 3, // Set Wednesday as the first day of the week
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridWeek,dayGridMonth' // Add toggle between Week and Month views
            },
            contentHeight: 'auto', // Adjusts height to fit content
            selectable: true,

            // Populate calendar with availability data from the database
            events: availabilityData.map(function(entry) {
                var eventTitle = entry.status === 'available' ? 
                    (entry.all_day ? 'Available All Day' : `Available ${entry.time_type} ${formatTime(entry.time)}`) : 'Unavailable';
                var eventColor = entry.status === 'available' ? 
                    (entry.all_day ? 'green' : 'yellow') : 'red';

                weeklyAvailability[entry.date] = {
                    status: entry.status,
                    all_day: entry.all_day,
                    time_type: entry.time_type,
                    time: entry.time,
                    title: eventTitle,
                    color: eventColor
                };

                return {
                    id: entry.date,
                    title: eventTitle,
                    start: entry.date,
                    allDay: true,
                    color: eventColor,
                    className: entry.time_type === 'after' ? 'fc-bg-yellow' : '',
                    extendedProps: {
                        status: entry.status,
                        all_day: entry.all_day,
                        time_type: entry.time_type,
                        time: entry.time
                    }
                };
            }),

            // Allow event clicks for editing
            eventClick: function(info) {
                var modal = document.getElementById("availabilityModal");
                var saveBtn = document.getElementById("saveAvailability");
                var selectedDateSpan = document.getElementById("selectedDate");

                selectedDateSpan.textContent = info.event.startStr;

                // Pre-fill modal fields with the event's current data
                var statusSelect = document.getElementById("status");
                var allDayCheckbox = document.getElementById("allDay");
                var timeInputsDiv = document.getElementById("timeInputs");
                var availabilityTypeSelect = document.getElementById("availabilityType");
                var availabilityTimeInput = document.getElementById("availabilityTime");

                statusSelect.value = info.event.extendedProps.status;
                allDayCheckbox.checked = info.event.extendedProps.all_day ? true : false;

                if (info.event.extendedProps.all_day || statusSelect.value === 'unavailable') {
                    timeInputsDiv.style.display = 'none';
                } else {
                    timeInputsDiv.style.display = 'block';
                    availabilityTypeSelect.value = info.event.extendedProps.time_type || 'after';
                    availabilityTimeInput.value = info.event.extendedProps.time || '';
                }

                modal.style.display = "block";

                // Save the changes when the user clicks "Save"
                saveBtn.onclick = function() {
                    var status = statusSelect.value;
                    var isAllDay = allDayCheckbox.checked;
                    var availabilityType = null;
                    var availabilityTime = null;
                    var allDay = null;

                    if (status === 'available') {
                        if (isAllDay) {
                            allDay = 1; // Set all_day to 1 if available all day
                        } else {
                            allDay = 0; // Set all_day to 0 if available after or until a specific time
                            availabilityType = availabilityTypeSelect.value;
                            availabilityTime = availabilityTimeInput.value;
                        }
                    }

                    // Update the event object in the calendar
                    info.event.setProp('title', status === 'available' ? (isAllDay ? 'Available All Day' : `Available ${availabilityType} ${formatTime(availabilityTime)}`) : 'Unavailable');
                    info.event.setProp('color', status === 'available' ? (isAllDay ? 'green' : 'yellow') : 'red');
                    info.event.setExtendedProp('status', status);
                    info.event.setExtendedProp('all_day', allDay);
                    info.event.setExtendedProp('time_type', availabilityType);
                    info.event.setExtendedProp('time', availabilityTime);

                    // Update the weeklyAvailability object
                    weeklyAvailability[info.event.startStr] = {
                        status: status,
                        all_day: allDay,
                        time_type: availabilityType,
                        time: availabilityTime,
                        title: info.event.title,
                        color: info.event.backgroundColor
                    };

                    // Close the modal
                    modal.style.display = "none";
                };
            },

            select: function(info) {
                // Open the modal
                var modal = document.getElementById("availabilityModal");
                var span = document.getElementsByClassName("close")[0];
                var saveBtn = document.getElementById("saveAvailability");
                var selectedDateSpan = document.getElementById("selectedDate");

                selectedDateSpan.textContent = info.startStr;

                modal.style.display = "block";

                // When the user clicks on <span> (x), close the modal
                span.onclick = function() {
                    modal.style.display = "none";
                }

                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }

                // Show/hide time inputs and "All Day" checkbox based on availability status
                var statusSelect = document.getElementById("status");
                var timeInputsDiv = document.getElementById("timeInputs");
                var allDayCheckbox = document.getElementById("allDay");
                var allDayOptionDiv = document.getElementById("allDayOption");

                function updateTimeInputs() {
                    if (statusSelect.value === 'available') {
                        allDayOptionDiv.style.display = 'flex';
                        if (allDayCheckbox.checked) {
                            timeInputsDiv.style.display = 'none';
                        } else {
                            timeInputsDiv.style.display = 'block';
                        }
                    } else {
                        allDayOptionDiv.style.display = 'none';
                        timeInputsDiv.style.display = 'none';
                    }
                }

                statusSelect.addEventListener('change', function() {
                    allDayCheckbox.checked = false; // Reset the "All Day" checkbox when status changes
                    updateTimeInputs();
                });

                allDayCheckbox.addEventListener('change', updateTimeInputs);

                // Save availability
                saveBtn.onclick = function() {
                    var status = statusSelect.value;
                    var isAllDay = allDayCheckbox.checked;
                    var availabilityType = null;
                    var availabilityTime = null;
                    var allDay = null;

                    if (status === 'available') {
                        if (isAllDay) {
                            allDay = 1; // Set all_day to 1 if available all day
                        } else {
                            allDay = 0; // Set all_day to 0 if available after or until a specific time
                            availabilityType = document.getElementById("availabilityType").value;
                            availabilityTime = document.getElementById("availabilityTime").value;
                        }
                    }

                    // Create the event object based on user input
                    var eventTitle = status === 'available' ? (isAllDay ? 'Available All Day' : `Available ${availabilityType} ${formatTime(availabilityTime)}`) : 'Unavailable';
                    var eventColor = status === 'available' ? (isAllDay ? 'green' : 'yellow') : 'red';

                    // Save the event details in the weeklyAvailability object
                    weeklyAvailability[info.startStr] = {
                        status: status,
                        all_day: allDay, // Set all_day to 1, 0, or null as needed
                        time_type: availabilityType, // Set to null if unavailable or available all day
                        time: availabilityTime, // Set to null if unavailable or available all day
                        title: eventTitle,
                        color: eventColor
                    };

                    // Render the event on the calendar
                    renderEvents(calendar);

                    // Close the modal
                    modal.style.display = "none";
                }

                // Initialize the modal with the correct visibility of time inputs and "All Day" checkbox
                updateTimeInputs();
            }
        });

        calendar.render();

        // Function to format time from 24-hour to 12-hour AM/PM format
        function formatTime(time) {
            if (!time) return '';
            var [hour, minute] = time.split(':');
            var ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12 || 12;
            return `${hour}:${minute} ${ampm}`;
        }

        // Function to render events on the calendar
        function renderEvents(calendar) {
            calendar.getEvents().forEach(event => event.remove()); // Clear existing events
            Object.keys(weeklyAvailability).forEach(date => {
                var event = {
                    id: date,
                    title: weeklyAvailability[date].title,
                    start: date,
                    allDay: true,
                    color: weeklyAvailability[date].color,
                    className: weeklyAvailability[date].color === 'yellow' ? 'fc-bg-yellow' : ''
                };
                calendar.addEvent(event);
            });
        }

        // Handle the submit button click
        document.getElementById("submitAvailability").onclick = function() {
            // Send the weeklyAvailability data to the server
            $.ajax({
                url: 'submit_availability.php',
                method: 'POST',
                data: { availability: weeklyAvailability },
                success: function(response) {
                    alert("Availability submitted successfully!");
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error submitting availability:", textStatus, errorThrown);
                    alert("There was an error submitting your availability. Please try again.");
                }
            });
        }

        //handle clear availability
        document.getElementById("clearAvailability").addEventListener('click', function() {
    if (confirm("Are you sure you want to clear all availability? This action cannot be undone.")) {
        $.ajax({
            url: 'clear_availability.php', // PHP script to handle clearing availability
            method: 'POST',
            data: { user_id: <?php echo $_SESSION['user_id']; ?> }, // Pass user ID
            success: function(response) {
                alert("Availability cleared successfully!");
                location.reload(); // Refresh the page to reflect changes
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error clearing availability:", textStatus, errorThrown);
                alert("There was an error clearing your availability. Please try again.");
            }
        });
    }
});


        
        
    });
</script>
</body>
</html>
