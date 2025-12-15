<?php
include 'db.php';

if (isset($_GET['fid'])) {
    $fid = intval($_GET['fid']); 
    $sql = "SELECT * FROM faculty WHERE fid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fid);
    $stmt->execute();
    $result = $stmt->get_result();
    $faculty = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $fid = intval($_POST["fid"]);
    $fidno = trim($_POST["fidno"]);
    $rfid = trim($_POST["rfid"]);
    $fname = trim($_POST["fname"]);
    $lname = trim($_POST["lname"]);

    // Validate and sanitize inputs
    if (!filter_var($fidno, FILTER_VALIDATE_INT)) {
        die("Invalid Faculty ID Number.");
    }

    // Check for duplicate fidno, excluding the current faculty member
    $check_fidno_sql = "SELECT * FROM faculty WHERE fidno = ? AND fid != ?";
    $check_fidno_stmt = $conn->prepare($check_fidno_sql);
    $check_fidno_stmt->bind_param("si", $fidno, $fid);
    $check_fidno_stmt->execute();
    $check_fidno_result = $check_fidno_stmt->get_result();

    if ($check_fidno_result->num_rows > 0) {
        echo "<script>alert('Faculty ID number already exists.');
        window.location.href = 'faculty.php';
        </script>";
        $check_fidno_stmt->close();
        exit();
    }

    // Check for duplicate rfid
    if ($rfid) {
        $check_rfid_sql = "SELECT * FROM faculty WHERE rfid = ? AND fid != ?";
        $check_rfid_stmt = $conn->prepare($check_rfid_sql);
        $check_rfid_stmt->bind_param("si", $rfid, $fid);
        $check_rfid_stmt->execute();
        $check_rfid_result = $check_rfid_stmt->get_result();

        if ($check_rfid_result->num_rows > 0) {
            echo "<script>alert('RFID already exists.');</script>";
            $check_rfid_stmt->close();
            exit();
        }
    }

    $sql = "UPDATE faculty SET fidno=?, rfid=?, fname=?, lname=? WHERE fid=?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("isssi", $fidno, $rfid, $fname, $lname, $fid);

    if (!$stmt->execute()) {
        die("Error executing query: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        header("Location: faculty.php");
        exit();
    } else {
        echo "<script>alert('No changes were made.');
        window.location.href = 'faculty.php';
        </script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Faculty</title>
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
    <h2>Update Faculty User</h2>
    <form method="post" action="facupdate.php">
        <input type="hidden" name="fid" value="<?php echo isset($faculty['fid']) ? $faculty['fid'] : ''; ?>">
        <center>
            <table class="table table-bordered mt-3">
                <tr>
                    <td>Faculty ID Number:</td>
                    <td><input type="number" name="fidno" autocomplete="off" class="form-control" value="<?php echo isset($faculty['fidno']) ? $faculty['fidno'] : ''; ?>" required></td>
                </tr>
                <!-- <tr>
                    <td>RFID:</td>
                    <td><input type="text" name="rfid" autocomplete="off" class="form-control" value="<?php echo isset($faculty['rfid']) ? $faculty['rfid'] : ''; ?>"></td>
                </tr> -->
                <tr>
                    <td>First Name:</td>
                    <td><input type="text" name="fname" autocomplete="off" class="form-control" value="<?php echo isset($faculty['fname']) ? $faculty['fname'] : ''; ?>" required></td>
                </tr>
                <tr>
                    <td>Last Name:</td>
                    <td><input type="text" name="lname" autocomplete="off" class="form-control" value="<?php echo isset($faculty['lname']) ? $faculty['lname'] : ''; ?>" required></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" name="update" class="btn btn-primary" value="Update"></td>
                </tr>
            </table>
        </center>
    </form>
</body>
</html>
