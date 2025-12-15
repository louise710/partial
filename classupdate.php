<?php
include 'db.php';

if (isset($_GET['cid'])) {
    $cid = intval($_GET['cid']);  
    $sql = "SELECT * FROM classuspen WHERE cid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $result = $stmt->get_result();
    $classuspen = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $cid = intval($_POST["cid"]); 
    $t_start = $_POST["t_start"];
    $t_end = $_POST["t_end"];
    $date = $_POST["date"];
    $class_desc = $_POST["class_desc"];

    $sql = "UPDATE classuspen SET t_start=?, t_end=?, date=?, class_desc=? WHERE cid=?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ssssi", $t_start, $t_end, $date, $class_desc, $cid);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: class.php");
        exit();
    } else {
        // Check for errors
        if ($stmt->error) {
            echo "Update failed: " . $stmt->error;
        } else {
            echo "<script>
                window.location.href = 'class.php'; 
              </script>";
        }
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Class</title>
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
    <h2>Update Class Suspension</h2>
    <form method="post" action="classupdate.php">
        <input type="hidden" name="cid" value="<?php echo htmlspecialchars($classuspen['cid'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <center><table class="table table-bordered mt-3">
            <tr>
                <td>Time Started:</td>
                <td><input type="time" name="t_start" autocomplete="off" value="<?php echo htmlspecialchars($classuspen['t_start'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required></td>
            </tr>
            <tr>
                <td>Time Ended:</td>
                <td><input type="time" name="t_end" autocomplete="off" value="<?php echo htmlspecialchars($classuspen['t_end'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required></td>
            </tr>
            <tr>
                <td>Date:</td>
                <td><input type="date" name="date" autocomplete="off" value="<?php echo htmlspecialchars($classuspen['date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required></td>
            </tr>
            <tr>
                <td>Description:</td>
                <td><input type="text" name="class_desc" autocomplete="off" value="<?php echo htmlspecialchars($classuspen['class_desc'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" name="update" class="btn btn-primary" value="Update"></td>
            </tr>
        </table></center>
    </form>
</body>
</html>
