<?php
session_start();
include 'db.php'; // Include your database connection
include 'navbar.php'; // Include your navbar

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Function to get events for a specific week
function getWeeklyEvents($startDate) {
    global $conn;
    $endDate = date('Y-m-d', strtotime($startDate . ' +6 days'));
    $sql = "SELECT * FROM weekly_events WHERE event_date BETWEEN ? AND ? ORDER BY event_date";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();
    return $events;
}

// Get the start date of the week (Wednesday)
$currentDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('last Wednesday'));
$weeklyEvents = getWeeklyEvents($currentDate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <style>
        .day-header {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .event-container {
            margin-bottom: 20px;
        }
        .event-row {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            margin-bottom: 10px;
        }
        .add-event-btn {
            margin-bottom: 10px;
        }
        #add-event-form {
            display: none; /* Initially hidden */
        }
    </style>
</head>
<body>

<div class="container mt-5">
<div class="container mt-5 mb-4 py-3 d-flex justify-content-between align-items-center">
    <a href="admin.php" class="btn btn-secondary">&larr; Back to Admin Dashboard</a>
    <h1 class="text-center flex-grow-1">Manage Weekly Events</h1>
    <div>
        <button class="btn btn-primary me-2" onclick="navigateWeek(-1)">Previous Week</button>
        <button class="btn btn-primary" onclick="navigateWeek(1)">Next Week</button>
    </div>
</div>



    <!-- Add Event Form -->
    <div id="add-event-form" class="card p-4 mb-4">
        <h3 id="form-title">Add New Event</h3>
        <div class="row">
            <div class="col-md-4 mb-3">
                <input type="date" class="form-control" id="new-event-date" placeholder="Select Date">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" id="new-client" placeholder="Client">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" id="new-location" placeholder="Location">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" id="new-hours-of-event" placeholder="Hours of Event">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" id="new-service-hours" placeholder="Service Hours">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" id="new-permit-address" placeholder="Permit Address">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" id="new-permit-number" placeholder="Permit Number">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" id="new-staff-needed" placeholder="Staff Needed">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" id="new-complimentary" placeholder="Complimentary">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" id="new-contact" placeholder="Contact">
            </div>
            <div class="col-md-12 mb-3">
                <textarea class="form-control" id="new-details" placeholder="Details" rows="3"></textarea>
            </div>
        </div>
        <button id="save-event-button" class="btn btn-primary" onclick="saveNewEvent()" disabled>Save Event</button>
        <button class="btn btn-secondary" onclick="toggleAddEventForm()">Cancel</button>
    </div>

    <div id="week-container">
        <?php
        // Display days from Wednesday to the next Tuesday
        $daysOfWeek = ['Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday'];
        foreach ($daysOfWeek as $index => $dayName) {
            $dayDate = date('Y-m-d', strtotime($currentDate . " +$index days"));
            echo "<div class='event-container' id='day-$dayDate'>";
            echo "<div class='day-header'><h3>$dayName " . date('n/j', strtotime($dayDate)) . "</h3></div>";
            
            // Display existing events for the day
            foreach ($weeklyEvents as $event) {
                if ($event['event_date'] == $dayDate) {
                    echo "<div class='event-row' id='event-{$event['id']}'>";
                    echo "<div class='row'>";
                    echo "<div class='col-md-3'><strong>Client:</strong> " . htmlspecialchars($event['client']) . "</div>";
                    echo "<div class='col-md-3'><strong>Location:</strong> " . htmlspecialchars($event['location']) . "</div>";
                    echo "<div class='col-md-3'><strong>Hours of Event:</strong> " . htmlspecialchars($event['hours_of_event']) . "</div>";
                    echo "<div class='col-md-3'><strong>Service Hours:</strong> " . htmlspecialchars($event['service_hours']) . "</div>";
                    echo "<div class='col-md-3'><strong>Permit Address:</strong> " . htmlspecialchars($event['permit_address']) . "</div>";
                    echo "<div class='col-md-3'><strong>Permit Number:</strong> " . htmlspecialchars($event['permit_number']) . "</div>";
                    echo "<div class='col-md-3'><strong>Staff Needed:</strong> " . htmlspecialchars($event['staff_needed']) . "</div>";
                    echo "<div class='col-md-3'><strong>Complimentary:</strong> " . htmlspecialchars($event['complimentary']) . "</div>";
                    echo "<div class='col-md-3'><strong>Contact:</strong> " . htmlspecialchars($event['contact']) . "</div>";
                    echo "<div class='col-md-12'><strong>Details:</strong> " . htmlspecialchars($event['details']) . "</div>";
                    echo "</div>";
                    echo "<button class='btn btn-primary btn-sm mt-2 me-2' onclick='editEvent(" . json_encode($event) . ")'>Edit</button>";
                    echo "<button class='btn btn-danger btn-sm mt-2' onclick='deleteEvent(" . $event['id'] . ")'>Delete</button>";
                    echo "</div>";
                }
            }
            echo "</div>";
        }
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function toggleAddEventForm() {
    const form = document.getElementById('add-event-form');
    const formTitle = document.getElementById('form-title');
    const saveButton = document.getElementById('save-event-button');

    if (form.style.display === 'none' || form.style.display === '') {
        // Show the form
        form.style.display = 'block';
        formTitle.textContent = 'Add New Event';

        // Reset the input fields
        document.getElementById('new-event-date').value = '';
        document.getElementById('new-client').value = '';
        document.getElementById('new-location').value = '';
        document.getElementById('new-hours-of-event').value = '';
        document.getElementById('new-service-hours').value = '';
        document.getElementById('new-permit-address').value = '';
        document.getElementById('new-permit-number').value = '';
        document.getElementById('new-staff-needed').value = '';
        document.getElementById('new-complimentary').value = '';
        document.getElementById('new-contact').value = '';
        document.getElementById('new-details').value = '';

        // Set save button for adding new event
        saveButton.setAttribute('onclick', 'saveNewEvent()');
        saveButton.setAttribute('disabled', true);

        // Disable all delete and edit buttons while the form is open
        document.querySelectorAll('.btn-danger, .btn-primary').forEach(button => button.setAttribute('disabled', true));
    } else {
        // Hide the form
        form.style.display = 'none';

        // Enable all delete and edit buttons when the form is closed
        document.querySelectorAll('.btn-danger, .btn-primary').forEach(button => button.removeAttribute('disabled'));
    }
}


function editEvent(event) {
    // Show the form
    toggleAddEventForm();

    // Change the form title to "Edit Event"
    document.getElementById('form-title').textContent = 'Edit Event';

    // Prefill the form with the event data, fallback to an empty string if value is undefined
    document.getElementById('new-event-date').value = event.event_date || '';
    document.getElementById('new-client').value = event.client || '';
    document.getElementById('new-location').value = event.location || '';
    document.getElementById('new-hours-of-event').value = event.hours_of_event || '';
    document.getElementById('new-service-hours').value = event.service_hours || '';
    document.getElementById('new-permit-address').value = event.permit_address || '';
    document.getElementById('new-permit-number').value = event.permit_number || '';
    document.getElementById('new-staff-needed').value = event.staff_needed || '';
    document.getElementById('new-complimentary').value = event.complimentary || '';
    document.getElementById('new-contact').value = event.contact || '';
    document.getElementById('new-details').value = event.details || '';

    // Enable the "Save Event" button
    document.getElementById('save-event-button').removeAttribute('disabled');

    // Update save button click handler to edit the existing event
    document.getElementById('save-event-button').setAttribute('onclick', `updateEvent(${event.id})`);
}



function updateEvent(eventId) {
    // Save the edited event using AJAX
    const eventDate = document.getElementById('new-event-date').value;
    const client = document.getElementById('new-client').value;
    const location = document.getElementById('new-location').value;
    const hoursOfEvent = document.getElementById('new-hours-of-event').value;
    const serviceHours = document.getElementById('new-service-hours').value;
    const permitAddress = document.getElementById('new-permit-address').value;
    const permitNumber = document.getElementById('new-permit-number').value;
    const staffNeeded = document.getElementById('new-staff-needed').value;
    const complimentary = document.getElementById('new-complimentary').value;
    const contact = document.getElementById('new-contact').value;
    const details = document.getElementById('new-details').value;

    // Send the edited event data to update_event.php
    $.post('update_event.php', {
        id: eventId,
        event_date: eventDate,
        client: client,
        location: location,
        hours_of_event: hoursOfEvent,
        service_hours: serviceHours,
        permit_address: permitAddress,
        permit_number: permitNumber,
        staff_needed: staffNeeded,
        complimentary: complimentary,
        contact: contact,
        details: details
    }, function(response) {
        // Assuming response is success if the event is updated
        alert('Event updated successfully!');

        // Update the event directly in the UI
        const eventRow = document.getElementById(`event-${eventId}`);
        eventRow.innerHTML = `
            <div class='row'>
                <div class='col-md-3'><strong>Client:</strong> ${client}</div>
                <div class='col-md-3'><strong>Location:</strong> ${location}</div>
                <div class='col-md-3'><strong>Hours of Event:</strong> ${hoursOfEvent}</div>
                <div class='col-md-3'><strong>Service Hours:</strong> ${serviceHours}</div>
                <div class='col-md-3'><strong>Permit Address:</strong> ${permitAddress}</div>
                <div class='col-md-3'><strong>Permit Number:</strong> ${permitNumber}</div>
                <div class='col-md-3'><strong>Staff Needed:</strong> ${staffNeeded}</div>
                <div class='col-md-3'><strong>Complimentary:</strong> ${complimentary}</div>
                <div class='col-md-3'><strong>Contact:</strong> ${contact}</div>
                <div class='col-md-12'><strong>Details:</strong> ${details}</div>
            </div>
            <button class='btn btn-primary btn-sm mt-2 me-2' onclick='editEvent(${JSON.stringify({
                id: eventId,
                event_date: eventDate,
                client: client,
                location: location,
                hours_of_event: hoursOfEvent,
                service_hours: serviceHours,
                permit_address: permitAddress,
                permit_number: permitNumber,
                staff_needed: staffNeeded,
                complimentary: complimentary,
                contact: contact,
                details: details
            })})'>Edit</button>
            <button class='btn btn-danger btn-sm mt-2' onclick='deleteEvent(${eventId})'>Delete</button>
        `;

        // Close the edit form
        toggleAddEventForm();
    }).fail(function() {
        alert('Failed to update event. Please try again.');
    });
}


function saveNewEvent() {
    // Save new event using AJAX
    const eventDate = document.getElementById('new-event-date').value;
    const client = document.getElementById('new-client').value;
    const location = document.getElementById('new-location').value;
    const hoursOfEvent = document.getElementById('new-hours-of-event').value;
    const serviceHours = document.getElementById('new-service-hours').value;
    const permitAddress = document.getElementById('new-permit-address').value;
    const permitNumber = document.getElementById('new-permit-number').value;
    const staffNeeded = document.getElementById('new-staff-needed').value;
    const complimentary = document.getElementById('new-complimentary').value;
    const contact = document.getElementById('new-contact').value;
    const details = document.getElementById('new-details').value;

    // Send the new event data to add_event.php
    $.post('add_event.php', {
        event_date: eventDate,
        client: client,
        location: location,
        hours_of_event: hoursOfEvent,
        service_hours: serviceHours,
        permit_address: permitAddress,
        permit_number: permitNumber,
        staff_needed: staffNeeded,
        complimentary: complimentary,
        contact: contact,
        details: details
    }, function(response) {
        // Assuming response is success if the event is added
        alert('Event added successfully!');

        // Close the add event form
        toggleAddEventForm();

        // Add the new event directly to the correct day in the UI
        const newEventHtml = `
            <div class='event-row' id='event-${response.id}'>
                <div class='row'>
                    <div class='col-md-3'><strong>Client:</strong> ${client}</div>
                    <div class='col-md-3'><strong>Location:</strong> ${location}</div>
                    <div class='col-md-3'><strong>Hours of Event:</strong> ${hoursOfEvent}</div>
                    <div class='col-md-3'><strong>Service Hours:</strong> ${serviceHours}</div>
                    <div class='col-md-3'><strong>Permit Address:</strong> ${permitAddress}</div>
                    <div class='col-md-3'><strong>Permit Number:</strong> ${permitNumber}</div>
                    <div class='col-md-3'><strong>Staff Needed:</strong> ${staffNeeded}</div>
                    <div class='col-md-3'><strong>Complimentary:</strong> ${complimentary}</div>
                    <div class='col-md-3'><strong>Contact:</strong> ${contact}</div>
                    <div class='col-md-12'><strong>Details:</strong> ${details}</div>
                </div>
                <button class='btn btn-primary btn-sm mt-2 me-2' onclick='editEvent(${JSON.stringify(response)})'>Edit</button>
                <button class='btn btn-danger btn-sm mt-2' onclick='deleteEvent(${response.id})'>Delete</button>
            </div>
        `;
        document.getElementById(`day-${eventDate}`).insertAdjacentHTML('beforeend', newEventHtml);
    }).fail(function() {
        alert('Failed to save event. Please try again.');
    });
}


function deleteEvent(eventId) {
    // Send an AJAX request to delete an event
    if (confirm('Are you sure you want to delete this event?')) {
        $.post('delete_event.php', { event_id: eventId }, function(response) {
            if (response === 'success') {
                alert('Event deleted successfully!');
                // Remove the event row from the UI
                document.getElementById(`event-${eventId}`).remove();
            } else {
                alert('Failed to delete event. Please try again.');
            }
        }).fail(function() {
            alert('Failed to delete event. Please try again.');
        });
    }
}

function navigateWeek(offset) {
    // Navigate to the next or previous week
    const currentDate = new Date('<?php echo $currentDate; ?>');
    currentDate.setDate(currentDate.getDate() + offset * 7);
    const newDateStr = currentDate.toISOString().split('T')[0];
    window.location.href = `manage_events.php?start_date=${newDateStr}`;
}

document.getElementById('new-event-date').addEventListener('input', function() {
    const saveButton = document.getElementById('save-event-button');
    if (this.value) {
        saveButton.removeAttribute('disabled');
    } else {
        saveButton.setAttribute('disabled', true);
    }
});

</script>
</body>
</html>
