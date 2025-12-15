<?php
include 'db.php';

    if (isset($_REQUEST["user_id"])) {    
        $idno = $_REQUEST["user_id"];

        $sql = "DELETE FROM user WHERE user_id=?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("i", $idno);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: user.php");
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


