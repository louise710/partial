<?php
include 'db.php';

if (isset($_GET['holiday_id'])) {
    $holiday_id = intval($_GET['holiday_id']);
    $sql = "SELECT * FROM holiday WHERE holiday_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $holiday_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $holiday = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $holiday_id = intval($_POST["holiday_id"]); 
    $date = $_POST["date"];
    $descript = $_POST["descript"];

    $sql = "UPDATE holiday SET date=?, descript=? WHERE holiday_id=?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ssi", $date, $descript, $holiday_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: holiday.php");
        exit();
    } else {
        // Check for errors
        if ($stmt->error) {
            echo "Update failed: " . $stmt->error;
        } else {
            echo "<script>
                alert('No changes made.');
                window.location.href = 'holiday.php';
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
    <title>Edit Holiday</title>
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
    <h2>Update Holiday</h2>
    <form method="post" action="holiupdate.php">
        <input type="hidden" name="holiday_id" value="<?php echo htmlspecialchars($holiday['holiday_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <center><table class="table table-bordered mt-3">
            <tr>
                <td>Date:</td>
                <td><input type="date" name="date" autocomplete="off" value="<?php echo htmlspecialchars($holiday['date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required></td>
            </tr>
            <tr>
                <td>Description:</td>
                <td><input type="text" name="descript" autocomplete="off" value="<?php echo htmlspecialchars($holiday['descript'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" name="update" class="btn btn-primary" value="Update"></td>
            </tr>
        </table></center>
    </form>
</body>
</html>
