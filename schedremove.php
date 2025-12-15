<?php
include 'db.php';

$rm_id = isset($_GET['id']) ? intval($_GET['id']) : null; 
    if (isset($_REQUEST["sched_id"])) {    
        $idno = $_REQUEST["sched_id"];

        $sql = "DELETE FROM sched WHERE sched_id=?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("i", $idno);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: " . $_SERVER['HTTP_REFERER']);    
            exit();
        } else {
            echo "Error deleting schedule: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "UID is not set in the form.";
    }
//}
?>


