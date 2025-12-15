<?php
session_start();
include_once "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$schedule_id = $_POST['schedule_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$schedule_id || !isset($status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Update schedule status
$query = "UPDATE sched SET stat = ? WHERE sched_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $status, $schedule_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>