<?php
// check_schedule.php
include 'db.php';

// Get current time and day
$current_time = date('H:i:s');
$current_day = date('N'); // 1=Monday, 7=Sunday
$day_map = [
    1 => 'M',  // Monday
    2 => 'T',  // Tuesday
    3 => 'W',  // Wednesday
    4 => 'Th', // Thursday
    5 => 'F',  // Friday
    6 => 'Sa', // Saturday
    7 => 'Su'  // Sunday
];
$current_day_letter = $day_map[$current_day];

// Get room ID from query parameter
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : null;

if ($room_id === null) {
    echo json_encode(['error' => 'Room ID required']);
    exit();
}

// Query to check if current time falls within any active schedule
$sql = "SELECT s.*, f.fname, f.lname, sub.code 
        FROM sched s
        JOIN faculty f ON s.fid = f.fid
        JOIN sub ON s.sub_id = sub.sid
        WHERE s.rm_id = ?
        AND s.stat = 1
        AND (FIND_IN_SET(?, s.day) > 0 OR s.day LIKE ?)
        AND s.time_in <= ?
        AND s.time_out >= ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

// Prepare pattern for day search
$day_pattern = '%' . $current_day_letter . '%';

$stmt->bind_param('issss', $room_id, $current_day_letter, $day_pattern, $current_time, $current_time);
$stmt->execute();
$result = $stmt->get_result();

$response = [
    'active' => false,
    'schedule' => null
];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response['active'] = true;
    $response['schedule'] = [
        'time_in' => $row['time_in'],
        'time_out' => $row['time_out'],
        'faculty' => $row['fname'] . ' ' . $row['lname'],
        'subject' => $row['code']
    ];
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>