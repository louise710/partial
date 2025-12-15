<?php
include 'db.php';

if (isset($_POST['fid']) && isset($_POST['currentStatus'])) {
    $fid = mysqli_real_escape_string($conn, $_POST['fid']);
    $currentStatus = mysqli_real_escape_string($conn, $_POST['currentStatus']);

    
    $newStatus = ($currentStatus == 1) ? 0 : 1;

    $updateQuery = "UPDATE faculty SET stat = ? WHERE fid = ?";
    $stmt = $conn->prepare($updateQuery);
    
    if ($stmt) {
        $stmt->bind_param("ii", $newStatus, $fid);
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
} else {
    // Send an error response if parameters are not set
    echo json_encode(['success' => false]);
}

$conn->close();

?>
