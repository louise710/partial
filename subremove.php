<?php
include 'db.php';

    if (isset($_REQUEST["sid"])) {    
        $sid = $_REQUEST["sid"];

        $sql = "DELETE FROM sub WHERE sid=?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("i", $sid);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: sub.php");
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


