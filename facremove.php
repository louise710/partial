<?php
include 'db.php';

    if (isset($_REQUEST["fid"])) {    
        $idno = $_REQUEST["fid"];

        
        $getSched = "SELECT COUNT(*) AS total_rows FROM sched WHERE fid = $idno";
        $result = $conn->query($getSched);

        $row = $result->fetch_assoc();
        
        if($row['total_rows'] > 0){
            echo "<script>
            alert('Faculty cannot be deleted because they are assigned in a schedule.');
            window.location.href = 'faculty.php';  
          </script>";

        }else{
        $sql = "DELETE FROM faculty WHERE fid=?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("i", $idno);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: faculty.php");
            exit();
        } else {
            echo "Error deleting user: " . $stmt->error;
        }

        $stmt->close();
    }


        
    } else {
        echo "UID is not set in the form.";
    }
//}
?>


