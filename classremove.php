<?php
include 'db.php';

    if (isset($_REQUEST["cid"])) {    
        $idno = $_REQUEST["cid"];

        $sql = "DELETE FROM classuspen WHERE cid=?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("i", $idno);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: class.php");
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


