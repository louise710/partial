<?php
include 'db.php';

if (isset($_POST['user_id']) && isset($_POST['currentStatus'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $currentStatus = mysqli_real_escape_string($conn, $_POST['currentStatus']);

    $newStatus = ($currentStatus == 1) ? 0 : 1;

    $updateQuery = "UPDATE user SET ustat = ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    
    if ($stmt) {
        $stmt->bind_param("ii", $newStatus, $user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'newStatus' => $newStatus]);
        } else {
            echo json_encode(['success' => false]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}

$conn->close();

?>
