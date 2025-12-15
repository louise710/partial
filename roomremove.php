<?php
include 'db.php';

    if (isset($_REQUEST["rm_id"])) {    
        $idno = $_REQUEST["rm_id"];

        $sql = "DELETE FROM room WHERE rm_id=?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("i", $idno);
        $stmt->execute();

        // Check if the query was successful
        if ($stmt->affected_rows > 0) {
            //echo "User deleted successfully!";
            header("Location: room.php");
            exit();
        } else {
            echo "Error deleting user: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "UID is not set in the form.";
    }
//}
?>


