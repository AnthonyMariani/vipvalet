<?php
session_start();
include 'db.php'; // Include your database connection
include 'navbar.php'; // Include your navbar

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Function to get weekly events
// Function to get weekly events with row count
function getWeeklyEvents($startDate) {
    global $conn;
    $endDate = date('Y-m-d', strtotime($startDate . ' +6 days'));
    $sql = "SELECT *, IFNULL(row_count, staff_needed) AS total_staff 
            FROM weekly_events 
            WHERE event_date BETWEEN ? AND ? 
            ORDER BY event_date";
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
    <title>Manage Weekly Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <style>
    body {
        margin-top: 70px; /* Adjust margin to avoid overlap with fixed navbar */
    }
    .container-schedule {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 70px); /* Adjust height to fill the screen, accounting for navbar */
        overflow: hidden; /* Prevent vertical scrolling */
    }
    .navigation-buttons {
        text-align: center;
        margin-bottom: 10px;
    }
    .schedule-wrapper {
        flex: 2; /* Take 2/3 of the remaining height */
        overflow-x: auto; /* Allow horizontal scrolling */
        overflow-y: auto; /* Allow vertical scrolling */
        max-height: 66vh; /* Set maximum height for the schedule wrapper */
    }
    .schedule-container {
        display: grid;
        grid-template-columns: repeat(7, 300px); /* Wider columns for better visibility */
        gap: 20px;
        margin-top: 20px;
        align-content: start; /* Make sure items align at the start for better readability */
    }
    .event-card {
        background-color: #ffffff;
        border: 6px solid #ddd; /* Default border color, thicker */
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 15px;
        cursor: pointer;
        position: relative;
    }
    .day-column {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #dee2e6;
    }
    .day-header {
        text-align: center;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .highlighted-event {
        border-width: 10px; /* Increase border width to make it more prominent */
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3); /* Add a shadow around the card */
        transform: scale(1.05); /* Slightly increase the size to indicate selection */
        transition: all 0.3s ease; /* Smooth transition effect */
    }
    .staff-row {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    .staff-row input.start-time {
        width: 30%; /* Start Time input takes 1/3 of the row */
        margin-right: 10px;
    }
    .staff-row input.employee-name {
        width: 65%; /* Employee Name input takes 2/3 of the row */
    }
    .event-footer {
        margin-top: 10px;
        text-align: center;
    }
    .event-footer button {
        margin: 0 5px;
    }
    .guest-row input {
        border: 2px solid red;
    }
    .available-employees {
        flex: 1; /* Take 1/3 of the remaining height */
        background-color: #f1f1f1;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #ccc;
        overflow: auto; /* Allow vertical scrolling for available employees */
    }
    .employee-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }
    .employee-box {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
    }
    .event-highlight-indicator {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
    }
    </style>
</head>
<body>

    <div class="container container-schedule mt-3">
        <div class="container mt-5">
            <div class="d-flex justify-content-center align-items-center">

                <!-- Navigation -->
                <div class="navigation-buttons">
                    <a href="admin.php" class="btn btn-secondary me-3">&larr; Back to Admin Dashboard</a>
                    <button class="btn btn-primary me-2" onclick="navigateWeek(-1)">Previous Week</button>
                    <button class="btn btn-primary me-2" onclick="navigateWeek(1)">Next Week</button>
                    <button class="btn btn-success" onclick="saveAllEvents()">Save All Changes</button>
                </div>
            </div>
        </div>

        <h1 class="text-center mt-4">Manage Weekly Schedule</h1>

    <!-- Schedule Wrapper for the grid -->
    <div class="schedule-wrapper">
        <div class="schedule-container">
            <?php
            $daysOfWeek = ['Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday'];
            foreach ($daysOfWeek as $index => $dayName) {
                $dayDate = date('Y-m-d', strtotime($currentDate . " +$index days"));
                echo "<div class='day-column'>";
                echo "<div class='day-header'>$dayName " . date('n/j', strtotime($dayDate)) . "</div>";

                // Display events for the day
                foreach ($weeklyEvents as $event) {
                    if ($event['event_date'] == $dayDate) {
                        echo "<div class='event-card' id='event-{$event['id']}' data-event-date='{$dayDate}' onclick='highlightEvent({$event['id']}, \"{$dayDate}\")'>";
                        echo "<strong>Client:</strong> " . htmlspecialchars($event['client']) . "<br>";
                        echo "<strong>Location:</strong> " . htmlspecialchars($event['location']) . "<br>";

// Staff rows
echo "<div class='staff-rows' id='staff-rows-{$event['id']}'>";
$totalStaff = (int)$event['total_staff'];
for ($i = 0; $i < $totalStaff; $i++) {
    echo "<div class='staff-row' id='staff-row-{$event['id']}-$i'>";
    echo "<input type='text' class='form-control form-control-sm start-time' placeholder='Start Time'>";
    echo "<input type='text' class='form-control form-control-sm employee-name' placeholder='Employee Name'>";
    echo "</div>";
}
echo "</div>";


                        // Add, Delete, buttons for employees
                        echo "<div class='event-footer'>";
                        echo "<button class='btn btn-primary btn-sm' onclick='addEmployee({$event['id']})'>Add</button>";
                        echo "<button class='btn btn-danger btn-sm' onclick='deleteEmployee({$event['id']})'>Delete</button>";
                        echo "</div>";
                        echo "</div>";
                    }
                }

                echo "</div>";
            }
            ?>
        </div>
    </div>

    <!-- Available Employees Section -->
    <div id="available-employees" class="available-employees">
        <h3>Available Employees</h3>
        <div class="employee-grid" id="employee-grid"></div>
    </div>
</div>

<script>
$(document).ready(function() {
    var availableEmployees = [];
    var assignedEmployees = {};
    const distinctColors = [
        '#FF5733', '#33FF57', '#3357FF', '#FF33FF', '#33FFFF', '#FFBD33', '#C70039', '#900C3F',
        '#581845', '#FFC300', '#DAF7A6', '#28B463', '#1F618D', '#F39C12', '#16A085', '#8E44AD',
        '#2ECC71', '#E74C3C', '#3498DB', '#D35400'
    ];
    var currentEventDate = null;

    function loadAvailableEmployees(date) {
    return $.post('get_available_employees.php', { date: date }, function(response) {
        try {
            availableEmployees = JSON.parse(response);

            // Assuming 'availability' gives specific time details for the day (like all day, after a specific time, etc.)
            // Set 'availabilityDates' to contain the current date for simplicity
            availableEmployees.forEach(function(emp) {
                emp.availabilityDates = [date];  // Only add current event date as available
            });

            console.log("Available Employees:", availableEmployees);

            // Initialize autocomplete if there are available employees
            if (availableEmployees.length > 0) {
                initializeAutocomplete();
            } else {
                console.warn("No available employees data to initialize autocomplete.");
            }

            // Update the UI with the available employees list
            updateAvailableEmployeesUI(availableEmployees);

        } catch (e) {
            console.error("Failed to parse available employees data.", e);
        }
    }).fail(function() {
        alert('Failed to load available employees.');
    });
}








function updateAvailableEmployeesUI(employees) {
    var employeeGrid = document.getElementById('employee-grid');
    employeeGrid.innerHTML = '';

    employees.forEach(function(employee) {
        var employeeBox = document.createElement('div');
        employeeBox.classList.add('employee-box');
        employeeBox.id = `employee-${employee.id}`;

        // Determine availability text
        let availabilityText = '';
        if (employee.availability_detail && employee.availability_detail !== "All Day") {
            availabilityText = ` (${employee.availability_detail})`; // Add specific availability, e.g., "Available after 5PM"
        }

        // Set employee box content with name and availability text (if applicable)
        employeeBox.textContent = `${employee.first_name} ${employee.last_name}${availabilityText}`;

        // Add dots for events assigned to the selected date
        if (assignedEmployees[currentEventDate] && assignedEmployees[currentEventDate][employee.id]) {
            assignedEmployees[currentEventDate][employee.id].forEach(eventColor => {
                var dot = document.createElement('span');
                dot.style.backgroundColor = eventColor;
                dot.style.width = '10px';
                dot.style.height = '10px';
                dot.style.display = 'inline-block';
                dot.style.borderRadius = '50%';
                dot.style.marginLeft = '5px';
                employeeBox.appendChild(dot);
            });
        }

        employeeGrid.appendChild(employeeBox);
    });

    // Show the available employees section
    document.getElementById('available-employees').style.display = 'block';
}






function initializeAutocomplete() {
    $('.employee-name').each(function() {
        var $inputField = $(this);

        // Destroy any existing autocomplete instance (if it exists)
        if ($inputField.data('ui-autocomplete')) {
            $inputField.autocomplete('destroy');
        }

        // Initialize autocomplete with filtered employee names based on availability
        $inputField.autocomplete({
            source: function(request, response) {
                // Get the current event date from the event card
                var eventCard = $inputField.closest('.event-card');
                var eventDate = eventCard.data('event-date');

                // Filter available employees based on the selected event date
                var filteredEmployees = availableEmployees.filter(function(emp) {
                    // Only include employees who are available on the specified event date
                    return emp.availabilityDates.includes(eventDate);
                }).map(function(emp) {
                    // Include availability details only if not available all day
                    var availabilityDetail = emp.availability && emp.availability !== "Available all day"
                        ? ` (${emp.availability})`
                        : '';

                    return {
                        label: `${emp.first_name} ${emp.last_name}${availabilityDetail}`,
                        value: `${emp.first_name} ${emp.last_name}`
                    };
                });

                console.log("Filtered employees for event date", eventDate, ":", filteredEmployees);

                // Provide filtered results to autocomplete
                response(filteredEmployees);
            },
            select: function(event, ui) {
                var selectedName = ui.item.value;
                $inputField.val(selectedName);
                var employeeId = findEmployeeId(selectedName);
                $inputField.data('employee-id', employeeId);
                assignEmployeeToEvent($inputField.closest('.event-card').attr('id'), employeeId);
            }
        });

        // Add listener for real-time changes to remove dots if an employee is removed
        $inputField.on('input', function() {
            var employeeName = $inputField.val();
            var previousEmployeeId = $inputField.data('employee-id');

            // If the input is cleared, remove the employee assignment
            if (!employeeName && previousEmployeeId) {
                $inputField.removeData('employee-id');
                var eventCard = $inputField.closest('.event-card');
                var eventId = eventCard.attr('id');
                var eventDate = eventCard.data('event-date');
                removeEmployeeFromEvent(eventId, previousEmployeeId, eventDate);
            }
        });
    });
}





function removeEmployeeFromEvent(eventId, employeeId, eventDate) {
    if (assignedEmployees[eventDate] && assignedEmployees[eventDate][employeeId]) {
        // Remove the event color from the assigned employee's list
        assignedEmployees[eventDate][employeeId] = assignedEmployees[eventDate][employeeId].filter(
            color => color !== $(`#${eventId}`).data('event-color')
        );

        // If no more events are assigned to that employee for the selected date, remove the entry
        if (assignedEmployees[eventDate][employeeId].length === 0) {
            delete assignedEmployees[eventDate][employeeId];
        }

        // If no more employees are assigned on that date, remove the date entry
        if (Object.keys(assignedEmployees[eventDate]).length === 0) {
            delete assignedEmployees[eventDate];
        }

        // Update the UI
        updateAvailableEmployeesUI(availableEmployees);
    }
}


    function findEmployeeId(name) {
        for (var i = 0; i < availableEmployees.length; i++) {
            var fullName = `${availableEmployees[i].first_name} ${availableEmployees[i].last_name}`;
            if (fullName === name) {
                return availableEmployees[i].id;
            }
        }
        return null;
    }

    function assignEmployeeToEvent(eventId, employeeId) {
        if (!employeeId) return;

        var eventCard = document.getElementById(eventId);
        var eventColor = eventCard ? eventCard.getAttribute('data-event-color') : '#000';
        var eventDate = eventCard.getAttribute('data-event-date');

        if (!assignedEmployees[eventDate]) {
            assignedEmployees[eventDate] = {};
        }

        if (!assignedEmployees[eventDate][employeeId]) {
            assignedEmployees[eventDate][employeeId] = [];
        }

        if (!assignedEmployees[eventDate][employeeId].includes(eventColor)) {
            assignedEmployees[eventDate][employeeId].push(eventColor);
        }

        updateAvailableEmployeesUI(availableEmployees);
    }



    window.highlightEvent = function(eventId, eventDate) {
    currentEventDate = eventDate;

    // Remove highlight from all events
    document.querySelectorAll('.event-card').forEach(function(card) {
        card.classList.remove('highlighted-event');
    });

    // Highlight the selected event
    var eventCard = document.getElementById(`event-${eventId}`);
    if (eventCard) {
        eventCard.classList.add('highlighted-event');
    }

    // Load available employees for the selected event's date
    loadAvailableEmployees(eventDate)
        .then(() => {
            console.log("Employees loaded successfully for event date:", eventDate);
        })
        .catch(() => {
            console.error("Error loading available employees.");
        });
};






window.addEmployee = function(eventId) {
    var staffRows = document.getElementById(`staff-rows-${eventId}`);
    var newIndex = staffRows.children.length;

    // Create a new employee row
    var newStaffRow = document.createElement('div');
    newStaffRow.classList.add('staff-row');
    newStaffRow.id = `staff-row-${eventId}-${newIndex}`;
    newStaffRow.innerHTML = `
        <input type='text' class='form-control form-control-sm start-time me-2' placeholder='Start Time'>
        <input type='text' class='form-control form-control-sm employee-name' placeholder='Employee Name'>
    `;
    staffRows.appendChild(newStaffRow);

    // Initialize autocomplete for the new row
    initializeAutocomplete();

    // Update the row count in the database
    $.post('update_row_count.php', {
        event_id: eventId,
        row_count: newIndex + 1 // Add the new row to the current count
    })
    .done(function() {
        console.log("Row count updated successfully.");
    })
    .fail(function() {
        console.error("Failed to update row count.");
    });
};


window.deleteEmployee = function(eventId) {
    var staffRows = document.getElementById(`staff-rows-${eventId}`);
    if (staffRows && staffRows.children.length > 0) {
        staffRows.removeChild(staffRows.lastChild);

        // Update the row count in the database
        $.post('update_row_count.php', {
            event_id: eventId,
            row_count: staffRows.children.length // Update with the new count after deletion
        })
        .done(function() {
            console.log("Row count updated successfully.");
        })
        .fail(function() {
            console.error("Failed to update row count.");
        });
    }
};


window.saveAllEvents = function() {
    // Array to collect all event assignments data
    let assignments = [];

    // Iterate through each event-card on the page
    $('.event-card').each(function() {
        let eventId = $(this).attr('id').replace('event-', '');

        // Iterate through each staff-row under this event-card
        $(this).find('.staff-row').each(function() {
            let employeeId = $(this).find('.employee-name').data('employee-id');
            let startTime = $(this).find('.start-time').val();

            // Only add the assignment if both event ID and employee ID are set
            if (employeeId && eventId) {
                assignments.push({
                    event_id: parseInt(eventId),
                    employee_id: parseInt(employeeId),
                    start_time: startTime ? startTime : null // If start time is empty, set to null
                });
            }
        });
    });

    // Log assignments to check correctness before sending
    console.log("Collected Assignments:", assignments);

    // Send the collected assignments to the server
    $.ajax({
        url: 'save_event_assignments.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(assignments),
        success: function(response) {
            console.log("Assignments saved successfully:", response);
            alert("Schedule saved successfully!");
        },
        error: function(error) {
            console.error("Error saving assignments:", error);
            alert("Failed to save the schedule. Please try again.");
        }
    });
}



window.navigateWeek = function(offset) {
    // Calculate the new date based on the offset (-1 for previous week, +1 for next week)
    const currentDate = new Date('<?php echo $currentDate; ?>');
    currentDate.setDate(currentDate.getDate() + offset * 7);
    const newDateStr = currentDate.toISOString().split('T')[0];

    // Redirect to the same page with the new start date parameter
    window.location.href = `manage_schedule.php?start_date=${newDateStr}`;
};


// Assign colors to event cards and add a visual highlight indicator
$('.event-card').each(function(index) {
    var color = distinctColors[index % distinctColors.length];
    $(this).css('border-color', color);
    $(this).css('border-width', '6px');
    $(this).attr('data-event-color', color);

    $(this).append(`<div class='event-highlight-indicator' style='background-color: ${color};'></div>`);
});

document.addEventListener('click', function(event) {
    if (
        !event.target.closest('.event-card') &&
        !$(event.target).closest('.ui-autocomplete').length &&
        !$(event.target).hasClass('ui-autocomplete-input')
    ) {
        document.querySelectorAll('.event-card').forEach(function(card) {
            card.classList.remove('highlighted-event');
        });
        document.getElementById('available-employees').style.display = 'none';
    }
});

});


</script>
</body>
</html>
