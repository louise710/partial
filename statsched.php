<?php
include 'db.php';

if (isset($_POST['sched_id']) && isset($_POST['currentStatus'])) {
    $sched_id = mysqli_real_escape_string($conn, $_POST['sched_id']);
    $currentStatus = mysqli_real_escape_string($conn, $_POST['currentStatus']);

    // Determine new status
    $newStatus = ($currentStatus == 1) ? 0 : 1;

    $updateQuery = "UPDATE sched SET stat = ? WHERE sched_id = ?";
    $stmt = $conn->prepare($updateQuery);
    
    if ($stmt) {
        $stmt->bind_param("ii", $newStatus, $sched_id);
        $stmt->execute();
        
        // Check if the update was successful
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'newStatus' => $newStatus]);
        } else {
            echo json_encode(['success' => false]);
        }

        $stmt->close();
    } else {
        // Send an error response
        echo json_encode(['success' => false]);
    }
} elseif (isset($_POST['create'])) {
    // Handle creating a new entry with default status of 1
    $createQuery = "INSERT INTO sched (stat) VALUES (1)"; // Assuming other fields are handled elsewhere
    if ($conn->query($createQuery) === TRUE) {
        echo json_encode(['success' => true, 'newSchedId' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    // Send an error response if parameters are not set
    echo json_encode(['success' => false]);
}

$conn->close();
?>
    