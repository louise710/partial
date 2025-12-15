<?php
include_once "db.php";

$device = $_GET['device'] ?? '';
$room_id = $_GET['room_id'] ?? 1;

// Map device codes to database device codes
$device_map = [
    'led1' => 'Light 1',
    'led2' => 'Light 2',
    'ac1' => 'AirCon 1', 
    'ac2' => 'AirCon 2'
];

$device_name = $device_map[$device] ?? $device;

echo "<h5>Schedules for {$device_name}</h5>";

// Get schedules for this device
$sql = "SELECT s.*, f.fname, f.lname, sub.code 
        FROM sched s
        LEFT JOIN faculty f ON s.fid = f.fid
        LEFT JOIN sub ON s.sub_id = sub.sid
        LEFT JOIN device_mapping dm ON s.device_id = dm.id
        WHERE s.rm_id = ? AND dm.device_code = ?
        ORDER BY s.day, s.time_in ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $room_id, $device);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<table class="table table-sm">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Day</th>
                    <th>Faculty</th>
                    <th>Subject</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
    
    while ($row = $result->fetch_assoc()) {
        $status = $row['stat'] == 1 ? 'Active' : 'Inactive';
        $status_class = $row['stat'] == 1 ? 'success' : 'danger';
        $initial = substr($row['fname'], 0, 1);
        
        echo "<tr>
                <td>{$row['time_in']} - {$row['time_out']}</td>
                <td>{$row['day']}</td>
                <td>{$initial}. {$row['lname']}</td>
                <td>{$row['code']}</td>
                <td><span class='badge bg-{$status_class}'>{$status}</span></td>
              </tr>";
    }
    
    echo '</tbody></table>';
} else {
    echo '<p>No schedules found for this device.</p>';
}

$stmt->close();
$conn->close();
?>