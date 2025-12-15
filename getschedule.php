<?php
session_start();
include_once "db.php";

// Get room_id from GET parameter
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : null;

if (!$room_id) {
    echo json_encode(['status' => 'error', 'message' => 'No room ID provided']);
    exit();
}

// Get current time and day
date_default_timezone_set('Asia/Manila'); // Adjust to your timezone
$current_time = date('H:i:s');
$current_day = date('l'); // Full day name (Monday, Tuesday, etc.)
$current_day_short = substr($current_day, 0, 3); // First 3 letters (Mon, Tue, etc.)

// Convert to uppercase to match your database format if needed
$current_day_short = strtoupper($current_day_short);

// Debug info
error_log("Room ID: " . $room_id);
error_log("Current Time: " . $current_time);
error_log("Current Day: " . $current_day);
error_log("Current Day Short: " . $current_day_short);

// Query to check if there's an active schedule for this room
$sql = "SELECT * FROM sched 
        WHERE rm_id = ? 
        AND day = ? 
        AND time_in <= ? 
        AND time_out >= ? 
        AND stat = 1 
        LIMIT 1";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement']);
    exit();
}

$stmt->bind_param('isss', $room_id, $current_day_short, $current_time, $current_time);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    echo json_encode(['status' => 'error', 'message' => 'Query execution failed']);
    exit();
}

$schedule_active = false;

if ($result->num_rows > 0) {
    $schedule_active = true;
    $schedule = $result->fetch_assoc();
    error_log("Active schedule found: " . print_r($schedule, true));
} else {
    error_log("No active schedule found");
}

$stmt->close();
$conn->close();

// Return JSON response
echo json_encode([
    'status' => 'success',
    'schedule_active' => $schedule_active,
    'current_time' => $current_time,
    'current_day' => $current_day,
    'room_id' => $room_id
]);
?>