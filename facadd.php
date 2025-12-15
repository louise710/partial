<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $fidno = trim($_POST["fidno"]);
    $fname = trim($_POST['fname']);
    $lname = trim($_POST["lname"]);
    $rfid = isset($_POST["rfid"]) ? $_POST["rfid"] : null;
    $stat = isset($_POST["stat"]) ? $_POST["stat"] : 0; // Default to inactive if not set

    // Check if fidno already exists
    $check_fidno_sql = "SELECT * FROM faculty WHERE fidno = ?";
    $check_fidno_stmt = $conn->prepare($check_fidno_sql);
    $check_fidno_stmt->bind_param("s", $fidno);
    $check_fidno_stmt->execute();
    $check_fidno_result = $check_fidno_stmt->get_result();

    if ($check_fidno_result->num_rows > 0) {
        echo "<script>alert('Faculty ID number already exists.');
        window.location.href = 'faculty.php';
        </script>";
        $check_fidno_stmt->close();
        exit();
    }

    // Check if rfid already exists
    if ($rfid) {
        $check_rfid_sql = "SELECT * FROM faculty WHERE rfid = ?";
        $check_rfid_stmt = $conn->prepare($check_rfid_sql);
        $check_rfid_stmt->bind_param("s", $rfid);
        $check_rfid_stmt->execute();
        $check_rfid_result = $check_rfid_stmt->get_result();

        if ($check_rfid_result->num_rows > 0) {
            echo "<script>alert('RFID already exists.');
            window.location.href = 'faculty.php';
            </script>";
            $check_rfid_stmt->close();
            exit();
        }
    }

    $sql = "INSERT INTO faculty (fidno, fname, lname, rfid, stat) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ssssi", $fidno, $fname, $lname, $rfid, $stat);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: faculty.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $check_fidno_stmt->close();
    if ($rfid) $check_rfid_stmt->close();
} else {
    echo "<script>console.log('PHP: Form not submitted.');</script>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Faculty</title>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
            margin: auto;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h2>Add Faculty User</h2>
    <form action="facadd.php" method="post">
        <table class="table table-bordered mt-3">
            <tr>
                <td><label for="fidno">Id Number:</label></td>
                <td><input type="number" name="fidno" id="fidno" class="form-control" required></td>
            </tr>
            <tr>
                <td><label for="fname">First Name:</label></td>
                <td><input type="text" name="fname" id="fname" class="form-control" required></td>
            </tr>
            <tr>
                <td><label for="lname">Last Name:</label></td>
                <td><input type="text" name="lname" id="lname" class="form-control" required></td>
            </tr>
            <tr>
                <td><label for="rfid">RFID:</label></td> 
                <td><input type="text" name="rfid" id="rfid" class="form-control"></td> 
            </tr>
            <tr>
                <td>Status:</td>
                <td>
                    <label><input type="radio" name="stat" value="1" checked> Active</label>
                    <label><input type="radio" name="stat" value="0"> Inactive</label>
                </td>
            </tr>
        </table>
        <center><input type="submit" name="add" value="Add Faculty User" class="btn btn-primary"></center>
    </form>
</body>
</html>
